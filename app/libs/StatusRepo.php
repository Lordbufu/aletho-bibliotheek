<?php

namespace App\Libs;

use App\App;

class StatusRepo {
    protected ?array        $statuses = null;
    protected \App\Database $db;

    public function __construct(\App\Database $db) {
        $this->db = $db;
    }

    /** Helper: Cache global $statuses */
    protected function setStatuses(): void {
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

    /** API: Reqeuest cached $statuses, either formatted (for display) or fully unformated (for logic) */
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

    /** API & Helper: Disable a specific `books_status` record */
    public function disableBookStatus(int $bookId): bool {
        $query = "UPDATE book_status SET active = 0 WHERE book_id = ? AND active = 1";
        $stmt = $this->db->query()->run($query, [$bookId]);
        return ($stmt->rowCount() > 0);
    }

    /** Get a book status type for a specific book */
    public function getBookStatus(int $bookId): ?string {
        $query = "SELECT s.type FROM book_status bs JOIN status s ON bs.status_id = s.id WHERE bs.book_id = ? AND bs.active = 1 LIMIT 1";
        $row = $this->db->query()->fetchOne($query,[$bookId]);
        return $row['type'] ?? null;
    }

    /** API: Get status by ID */
    public function getStatusById(int $statusId): ?array {
        $query = "SELECT * FROM status WHERE id = ? LIMIT 1";
        $row = $this->db->query()->fetchOne($query, [$statusId]);
        return $row ?: null;
    }

    /** Get the books status expire date */
    public function getBookDueDate(int $bookId): ?string {
        $queryBoLo  = "SELECT end_date, status_id FROM book_loaners WHERE book_id = ? AND active = 1 LIMIT 1";
        $row        = $this->db->query()->fetchOne($queryBoLo, [$bookId]);

        if (!$row || $row['end_date'] === null) {
            if ($row && $row['end_date'] === null) {
                error_log("Missing end_date in getBookDueDate for book_id={$bookId}");
            }
            
            $queryBoSt = "SELECT status_id FROM book_status WHERE book_id = ? AND active = 1 LIMIT 1";
            $bookStatusId = $this->db->query()->value($queryBoSt, [$bookId]);

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

    /** Update status period settings */
    public function updateStatusPeriod(int $statusId, ?int $periode_length, ?int $reminder_day, ?int $overdue_day): bool {
        $query  = "UPDATE status SET periode_length = ?, reminder_day = ?, overdue_day = ? WHERE id = ?";
        $params = [$periode_length, $reminder_day, $overdue_day, $statusId];
        return $this->db->query()->run($query, $params) !== false;
    }

    /** API: To set a book status we need (for now): */
    public function setBookStatus(int $bookId, int $statusId): bool {
        $disabled = $this->disableBookStatus($bookId);

        if (!$disabled) return false;

        $newStatus = $this->insertBookStatus($bookId, $statusId);

        if (!$newStatus) return false;

        return true;
    }

    /** API: Update contextual fields for an existing book_status record */
    public function updateBookStatusContext(int $bookStatusId, array $actionContext = [], ?bool $finished = null): bool {
        $fields = [];
        $params = [];

        // Handle action context keys dynamically
        if (isset($actionContext['action_type'])) {
            $fields[] = "action_type = ?";
            $params[] = $actionContext['action_type'];
        }

        if (isset($actionContext['action_token'])) {
            $fields[] = "action_token = ?";
            $params[] = $actionContext['action_token'];
        }

        if (isset($actionContext['token_expires']) && $actionContext['token_expires'] instanceof \DateTimeInterface) {
            $fields[] = "token_expires = ?";
            $params[] = $actionContext['token_expires']->format('Y-m-d H:i:s');
        }

        if (isset($actionContext['token_used'])) {
            $fields[] = "token_used = ?";
            $params[] = $actionContext['token_used'] ? 1 : 0;
        }

        // Handle finished flag separately
        if ($finished !== null) {
            $fields[] = "finished = ?";
            $params[] = $finished ? 1 : 0;
        }

        if (empty($fields)) {
            return false; // nothing to update
        }

        $params[] = $bookStatusId;
        $query = "UPDATE book_status SET " . implode(", ", $fields) . " WHERE id = ?";

        $stmt = $this->db->query()->run($query, $params);
        return ($stmt->rowCount() > 0);
    }
}