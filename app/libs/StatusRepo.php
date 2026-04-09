<?php
namespace App\Libs;

use App\Libs\Context\StatusContext;

final class StatusRepo {
    private \App\Database $db;

    public function __construct() {
        $this->db = \App\App::getService('database');
    }

    private function mapRowToStatus($row): StatusContext {
        $ctx = new StatusContext();
        $ctx->id           = (int)$row['id'];
        $ctx->type         = $row['type'];
        $ctx->periodLength = (int)$row['period_length'];
        $ctx->reminderDay  = (int)$row['reminder_day'];
        $ctx->overdueDay   = (int)$row['overdue_day'];
        return $ctx;
    }

    /** API: Get all `status` table data */
    public function getAll(): array {
        return $this->db->query()->fetchAll("
            SELECT id, type, period_length, reminder_day, overdue_day
            FROM status
            ORDER BY type ASC
        ");
    }

    /** API: Get a specific `status` raw row based on a id */
    public function getStatusRowById(int $statusId): ?array {
        return $this->db->query()->fetchOne("
            SELECT id, type, period_length, reminder_day, overdue_day
            FROM status
            WHERE id = :id
            LIMIT 1
        ", ['id' => $statusId]);
    }

    /** API: Get a formatted `status` context object based on a id */
    public function getStatusById(int $statusId): ?StatusContext {
        $row = $this->getStatusRowById($statusId);
        return $row ? $this->mapRowToStatus($row) : null;
    }

    /** API: Update a `status` row */
    public function updatePeriod(int $id, int $period, int $reminder, int $overdue): void {
        $sql = "
            UPDATE status
            SET period_length = :p,
                reminder_day  = :r,
                overdue_day   = :o
            WHERE id = :id
        ";

        $this->db->query()->run($sql, [
            'p'  => $period,
            'r'  => $reminder,
            'o'  => $overdue,
            'id' => $id
        ]);
    }
    
    // This is not only `status` table related
    /** API: Return all active status rows for a set of books */
    public function getActiveStatuses(array $bookIds): ?array {
        if (!$bookIds) return [];

        $sql = "
            SELECT bs.id, bs.book_id, bs.status_id, bs.active, bs.finished, s.type
            FROM book_status bs
            JOIN status s ON s.id = bs.status_id
            WHERE bs.book_id IN (" . implode(',', $bookIds) . ")
            AND bs.active = 1
            AND bs.finished = 0
        ";

        return $this->db->query()->fetchAll($sql);
    }
}