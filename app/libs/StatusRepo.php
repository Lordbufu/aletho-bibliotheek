<?php

namespace App\Libs;

use App\App;

class StatusRepo {
    protected ?array        $statuses = null;
    protected \App\Database $db;

    public function __construct() {
        $this->db = App::getService('database');

        if ($this->statuses === null) {
            $this->getAllstatuses();
        }
    }

    /*  Helper: calculate due date for setting the correct end_date (might get removed later) */
    private function calculateDueDate(string $startDate, int $days): string {
        $dt = new \DateTimeImmutable($startDate);
        return $dt->add(new \DateInterval("P{$days}D"))->format('Y-m-d');
    }

    /*  Cache all statuses */
    public function getAllStatuses(): array {
        if ($this->statuses === null) {
            $this->statuses = $this->db->query()->fetchAll("SELECT * FROM status");
        }

        return $this->statuses;
    }

    /*  Get a book status type for a specific book */
    public function getBookStatus(int $bookId): ?string {
        $row = $this->db->query()->fetchOne(
            "SELECT s.type FROM book_status bs JOIN status s ON bs.status_id = s.id WHERE bs.book_id = ? AND bs.active = 1 LIMIT 1",
            [$bookId]
        );

        return $row['type'] ?? null;
    }

    /*  Get the books status expire date */
    public function getBookDueDate(int $bookId): ?string {
        $row = $this->db->query()->fetchOne(
            "SELECT end_date, status_id FROM book_loaners WHERE book_id = ? AND active = 1 LIMIT 1",
            [$bookId]
        );

        if (!$row || $row['end_date'] === null) {
            if ($row && $row['end_date'] === null) {
                error_log("Missing end_date in getBookDueDate for book_id={$bookId}");
            }
            
            $bookStatusId = $this->db->query()->value(
                "SELECT status_id FROM book_status WHERE book_id = ? AND active = 1 LIMIT 1",
                [$bookId]
            );

            if ((int)$bookStatusId === 1) {
                return (new \DateTimeImmutable())->format('Y-m-d');
            }

            return null;
        }

        // Temp code block, to atleast have a end_date for the overdue status
        $statusId = (int)$row['status_id'];
        if ($statusId === 6) {
            return (new \DateTimeImmutable('yesterday'))->format('Y-m-d');
        }
        
        return (new \DateTimeImmutable($row['end_date']))->format('Y-m-d');
    }

    /*  Update status period settings */
    public function updateStatusPeriod(int $statusId, ?int $periode_length, ?int $reminder_day, ?int $overdue_day): bool {
        $sql = "UPDATE status SET periode_length = ?, reminder_day = ?, overdue_day = ? WHERE id = ?";
        return $this->db->query()->run($sql, [$periode_length, $reminder_day, $overdue_day, $statusId]) !== false;
    }

    /*  To set a book status we need (for now): */
    public function setBookStatus(int $bookId, int $statusId, array $loaner = [], array $context = []): bool {
        // Attempt to populate the loaner, if that data was set.
        if (!empty($loaner)) {
            $cLoaner = $this->db->query()->fetchOne("SELECT * FROM loaners WHERE name = ?", [$loaner['loaner_name']]);
            $status = $this->db->query()->fetchOne("SELECT periode_length FROM status WHERE id = ?",[$statusId]);
            $startDate = (new \DateTimeImmutable())->format('Y-m-d');
            $endDate   = $this->calculateDueDate($startDate, (int)($status['periode_length'] ?? 0));

            $this->db->query()->run(
                "INSERT INTO book_loaners (book_id, loaner_id, status_id, start_date, end_date, active)
                VALUES (?, ?, ?, ?, ?, 1)",
                [$bookId, $cLoaner['id'], $statusId, $startDate, $endDate]
            );
        }

        // Id need to evaluate this, but for now im setting old status info for the same id to inactive.
        $this->db->query()->run("UPDATE book_status SET active = 0 WHERE book_id = ? AND active = 1", [$bookId]);

        // If no context is provided, that the easy update route.
        if (empty($context)) {
            $sqlInsert = "INSERT INTO book_status (book_id, status_id, active) VALUES (?, ?, 1)";
            $result = $this->db->query()->run($sqlInsert, [$bookId, $statusId]);
        }

        return (bool)$result;
    }
}