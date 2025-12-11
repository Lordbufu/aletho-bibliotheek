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

    /** Helper: Resolve final status the book should have based on transport criteria */
    protected function resolveFinalStatus(int $requestedStatusId, bool $transport): int {
        return $transport ? 3 : $requestedStatusId;
    }

    /** Helper: Resolve if event should be linked ? */
    protected function shouldLinkEvent(int $requestedStatusId): bool {
        return $requestedStatusId !== 1;
    }

    /** Helper: Find the correct event key, in the pre-defined statusMap */
    protected function findEventKey(array $eventStatusMap, int $finalStatusId, ?int $oldStatus = null, ?string $currentTrigger = null): ?string {
        foreach ($eventStatusMap as $eventKey => $config) {
            $statuses = $config['status'];

            if (count($statuses) === 1) {
                if ($statuses[0] === $finalStatusId) {
                    return $eventKey;
                }
            }

            if (count($statuses) === 2) {
                if ($statuses[0] === $oldStatus && $statuses[1] === $finalStatusId) {
                    return $eventKey;
                }
            }
        }

        return null;
    }

    /** Helper: Resolve the event key from the `eventStatusMap` */
    protected function resolveEventKey(int $finalStatusId, int $oldStatus, string $trigger): ?string {
        $eventStatusMap = App::getService('status')->getEventStatusMap();
        return $this->findEventKey($eventStatusMap, $finalStatusId, $oldStatus, $trigger);
    }

    /** Helper: Resolve the correct notification id */
    protected function resolveEventKeyId(?string $eventKey): ?int {
        if (!$eventKey) {
            return null;
        }

        return App::getLibrary('notification')->getNotiIdByType($eventKey);
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

    /** API: Get status by Id */
    public function getStatusById(int $statusId): ?array {
        $query = "SELECT * FROM status WHERE id = ? LIMIT 1";
        $row = $this->db->query()->fetchOne($query, [$statusId]);
        return $row ?: null;
    }

    /** API: Get a book status type for a specific book */
    public function getBookStatus(int $bookId, string $flag = "type"): ?string {
        $query = "SELECT s.id, s.type FROM book_status bs JOIN status s ON bs.status_id = s.id WHERE bs.book_id = ? AND bs.active = 1 LIMIT 1";
        $row = $this->db->query()->fetchOne($query,[$bookId]);

        if ($flag !== "type") {
            return (int)$row['id'] ?? null;
        }

        return $row['type'] ?? null;
    }

    /** API: Request status_noti links, based on `book_status`.`id` and `status`.`id` */
    public function getStatusLinks(int $bookStatusId, int $statusId): array {
        $query  = "
            SELECT sn.notification_id, n.type
            AS event FROM status_noti sn
            JOIN notifications n ON sn.notification_id = n.id
            WHERE sn.bk_st_id = ? AND sn.status_id = ?
        ";
        $params = [$bookStatusId, $statusId];
        $stmt   = $this->db->query()->fetchAll($query, $params);
        return $stmt ?? null;
    }

    /** API & Helper: Disable a specific `books_status` record */
    public function disableBookStatus(int $bookId): bool {
        $query = "UPDATE book_status SET active = 0 WHERE book_id = ? AND active = 1";
        $stmt = $this->db->query()->run($query, [$bookId]);
        return ($stmt->rowCount() > 0);
    }

    /** Get the books status expire date */
    public function getBookDueDate(int $bookId): ?string {
        $queryBoLo  = "SELECT end_date, status_id FROM book_loaners WHERE book_id = ? AND active = 1 LIMIT 1";
        $row        = $this->db->query()->fetchOne($queryBoLo, [$bookId]);

        if (!$row || $row['end_date'] === null) {
            // Temp debug line
            // if ($row && $row['end_date'] === null) {
            //     error_log("Missing end_date in getBookDueDate for book_id={$bookId}");
            // }
            
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

    /** API: Update status_noti links, based on `book_status`.`id` and `notification`.`id` */
    public function updateStatusLinks(int $bookStatusId, int $notifId): ?int {
        $query  = "UPDATE status_noti SET mail_send = 1, sent_at = NOW() WHERE bk_st_id = ? AND notification_id = ?";
        $params = [$bookStatusId, $notifId];
        $stmt   = $this->db->query()->run($query, $params);
        return ($stmt->rowCount() > 0);
    }

    /** API: To set a empty `book_status` */
    public function setBookStatus(int $bookId, int $statusId): ?int {
        $disabled = $this->disableBookStatus($bookId);

        if (!$disabled) return null;

        $newStatus = $this->insertBookStatus($bookId, $statusId);

        if (!$newStatus) return null;

        return $newStatus;
    }

    /** API: Evaluate the required status, and update `book_status` using `setBookStatus` */
    public function updateBookStatus(int $bookId, int $requestedStatusId, bool $transport): array {
        $finalStatusId  = $this->resolveFinalStatus($requestedStatusId, $transport);
        $statusRecordId = $this->setBookStatus($bookId, $finalStatusId);

        return [
            'record_id'      => $statusRecordId,
            'final_status_id'=> $finalStatusId
        ];
    }

    /** API: Set `status_noti` to link notifications to a `book_status` if needed */
    public function linkEventIfNeeded(array $statusUpdate, int $requestedStatusId, int $oldStatus, string $trigger, array $requestStatus): ?int {
        if (!$this->shouldLinkEvent($requestedStatusId)) {
            return null;
        }

        $statusResult   = $statusUpdate['record_id'];
        $finalStatusId  = $statusUpdate['final_status_id'];

        $eventKey = $this->resolveEventKey($finalStatusId, $oldStatus, $trigger);
        $eventKeyId = $this->resolveEventKeyId($eventKey);

        if ($eventKeyId && !empty($requestStatus)) {
            $this->setStatusEvent($statusResult, $finalStatusId, $eventKeyId);
        }

        return $eventKeyId;
    }

    /** API: Set `status_noti` to link notifications to a `book_status` */
    public function setStatusEvent(int $bookStatusId, int $statusId, int $notificationId): bool {
        $sql    = "INSERT INTO status_noti (bk_st_id, status_id, notification_id, mail_send, sent_at) VALUES (?, ?, ?, 0, NULL)";
        $params = [$bookStatusId, $statusId, $notificationId];
        $stmt   = $this->db->query()->run($sql, $params);

        if ($stmt->rowCount() === 0) {
            error_log("[StatusRepo] Failed to insert status_noti for bk_st_id={$bookStatusId}, status_id={$statusId}, notification_id={$notificationId}");
        }

        return ($stmt->rowCount() > 0);
    }

    /** API: Get all notifications configured for a given status */
    public function getNotificationsForStatus(int $statusId): array {
        $sql = "SELECT n.id AS notification_id,
                    n.type AS event,
                    mt.id AS template_id,
                    mt.subject,
                    mt.from_mail,
                    mt.from_name
                FROM notifications n
                JOIN mail_templates mt ON n.template_id = mt.id
                WHERE n.active = 1
                AND EXISTS (
                    SELECT 1
                    FROM status_noti sn
                    WHERE sn.status_id = ?
                        AND sn.notification_id = n.id
                )";

        return $this->db->query()->fetchAll($sql, [$statusId]);
    }

    /** API: Update contextual fields for an existing book_status record (W.I.P.) */
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