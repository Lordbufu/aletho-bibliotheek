<?php

namespace App\Libs;

use App\App;

class StatusRepo {
    protected ?array        $statuses = null;
    protected \App\Database $db;

    public function __construct(\App\Database $db) {
        $this->db = $db;
    }

    /** Helper: calculate due date for setting the correct end_date (might get removed later) */
    protected function calculateDueDate(string $startDate, int $days): string {
        $dt = new \DateTimeImmutable($startDate);
        return $dt->add(new \DateInterval("P{$days}D"))->format('Y-m-d');
    }

    /** Helper: Cache global $statuses */
    protected function setStatuses(): array {
        $query = "SELECT * FROM status";
        $this->statuses = $this->db->query()->fetchAll($query);
    }

    /** Helper: Insert new `book_status` record */
    protected function insertBookStatus(int $bookId, int $statusId): ?int {
        $query  = "INSERT INTO book_status (book_id, status_id, active) VALUES (?, ?, 1)";
        $params = [$bookId, $statusId];

        $stmt = $this->db->query()->run($query, $params);

        if ($stmt->rowCount() === 0) {
            return null;
        }

        return (int)$this->db->connection()->pdo()->lastInsertId();
    }

    /** Reqeuest cached $statuses, formated with only id and type. */
    public function getAllStatuses($tag = null): array {
        if ($this->statuses === null) {
            $this->setStatuses();
        }

        /* Filter only id & type for filling in <select> inputs */
        if ($tag === 'idType') {
            $filtered = array_map(function ($status) {
                return [
                    'id'   => $status['id'],
                    'type' => $status['type'],
                ];
            }, $this->statuses);

            return $filtered;
        }

        return $this->statuses;
    }

    /** Get a book status type for a specific book */
    public function getBookStatus(int $bookId): ?string {
        $query = "SELECT s.type FROM book_status bs JOIN status s ON bs.status_id = s.id WHERE bs.book_id = ? AND bs.active = 1 LIMIT 1";
        $row = $this->db->query()->fetchOne($query,[$bookId]);
        return $row['type'] ?? null;
    }

    /** Get the books status expire date */
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

    /** Swap state on status objects */
    public function swapStatusActiveState(int $statusId): bool {
        $query  = "UPDATE status SET active = CASE WHEN active = 1 THEN 0 ELSE 1 END WHERE id = ?";
        $stmt = $this->db->query()->run($query, [$statusId]);
        return ($stmt->rowCount() > 0);
    }

    /** Disable books_status record */
    public function disableBookStatus(int $bookId): bool {
        $query = "UPDATE book_status SET active = 0 WHERE book_id = ? AND active = 1";
        $stmt = $this->db->query()->run($query, [$bookId]);
        return ($stmt->rowCount() > 0);
    }

    /** Update status period settings */
    public function updateStatusPeriod(int $statusId, ?int $periode_length, ?int $reminder_day, ?int $overdue_day): bool {
        $query  = "UPDATE status SET periode_length = ?, reminder_day = ?, overdue_day = ? WHERE id = ?";
        $params = [$periode_length, $reminder_day, $overdue_day, $statusId];
        return $this->db->query()->run($query, $params) !== false;
    }

    /** To set a book status we need (for now): */
    public function setBookStatus(int $bookId, int $statusId, array $loaner = [], array $context = []): bool {
        dd('testing error loops');
        /** Attempt to deactivate current status, return to caller if failed */
        $disabled = $this->disableBookStatus($bookId);

        if (!$disabled) return false;

        /** Attempt to insert the new `book_status` record, return to caller if failed */
        $newStatus = $this->insertBookStatus($bookId, $statusId);

        if (!$newStatus) return false;


        // Handle loaner logic
        if (!empty($loaner)) {
            // Resolve or create loaner based on e-mail
            $cLoaner = $this->loanerRepo->findOrCreateByEmail(
                $loaner['loaner_name'],
                $loaner['loaner_email'],
                (int)$loaner['loaner_location']
            );

            // Get status metadata
            $status = $this->db->query()->fetchOne("SELECT periode_length FROM status WHERE id = ?", [$statusId]);
            $startDate = (new \DateTimeImmutable())->format('Y-m-d');
            $endDate = $this->calculateDueDate($startDate, (int)($status['periode_length'] ?? 0));

            // Insert loaner record
            $this->db->query()->run(
                "INSERT INTO book_loaners (book_id, loaner_id, status_id, start_date, end_date, active)
                VALUES (?, ?, ?, ?, ?, 1)",
                [$bookId, $cLoaner['id'], $statusId, $startDate, $endDate]
            );
        } else {
            // If status is 'Aanwezig', deactivate any active loaner rows
            $statusType = $this->db->query()->fetchOne("SELECT type FROM status WHERE id = ?", [$statusId]);
            if ($statusType['type'] === 'Aanwezig') {
                $this->db->query()->run(
                    "UPDATE book_loaners SET active = 0 WHERE book_id = ? AND active = 1",
                    [$bookId]
                );
            }
        }

        return true;
    }
}

            // // Try to resolve loaner by email
            // $cLoaner = $this->db->query()->fetchOne("SELECT * FROM loaners WHERE email = ?", [$loaner['loaner_email']]);


            // if (!$cLoaner) {
            //     // Insert new loaner
            //     $this->db->query()->run(
            //         "INSERT INTO loaners (name, email, office_id, active) VALUES (?, ?, ?, 1)",
            //         [$loaner['loaner_name'], $loaner['loaner_email'], $loaner['loaner_location']]
            //     );

            //     $cLoaner = $this->db->query()->fetchOne("SELECT * FROM loaners WHERE email = ?", [$loaner['loaner_email']]);
            // }