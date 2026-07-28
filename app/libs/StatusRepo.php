<?php
namespace App\Libs;

use App\Libs\Context\StatusContext;

final class StatusRepo {
    private \App\Database $db;

    public function __construct() {
        $this->db = \App\App::getService('database');
    }

    // Re-factor status: tested and working
    /** API: Shared getAllStatuses() function, either get all raw rows, or specifically only the active marked once. */
    public function getStatuses(string $mode): array {
        $sql = match ($mode) {
            'all'    => "SELECT * FROM status",
            'active' => "SELECT * FROM status WHERE is_active = 1",
            default  => throw new \InvalidArgumentException("Invalid mode '$mode'")
        };

        $rows = $this->db->query()->fetchAll($sql);

        return array_map(
            fn($row) => StatusContext::fromRow($row),
            $rows
        );
    }

    // Re-factor status: tested and working
    /** API: Get active status by book_id */
    public function getBookStatusByBookId(mixed $book_ids): array {
        if (!$book_ids) {
            return [];
        }

        $book_ids = is_array($book_ids) ? $book_ids : [$book_ids];
        $book_ids = array_map('intval', $book_ids);
        $placeholders = [];
        $params = [];

        foreach ($book_ids as $i => $id) {
            $key = "book_id{$i}";
            $placeholders[] = ":{$key}";
            $params[$key] = $id;
        }

        $sql = "
            SELECT *
            FROM book_status bs
            JOIN status s ON bs.status_id = s.status_id
            WHERE bs.book_id IN (" . implode(',', $placeholders) . ")
            AND bs.is_active = 1
        ";

        $rows = $this->db->query()->fetchAll($sql, $params);

        $out = array_fill_keys($book_ids, null);

        foreach ($rows as $r) {
            $bid = (int)$r['book_id'];
            $out[$bid] = StatusContext::fromRow($r);
        }

        return $out;
    }

    // Re-factor status: tested and working
    /** API: Get a formatted `status` context object based on a id */
    public function getStatusById(int $status_id): ?StatusContext {
        $row = $this->db->query()->fetchOne("SELECT * FROM status WHERE status_id = :id LIMIT 1", ['id' => $status_id]);
        return $row ? new StatusContext($row) : null;
    }

    // Re-factor status: tested and working
    /** API: Update a `status` row */
    public function updateStatusPeriod(int $status_id, array $changes): void {
        $fields = [];
        $params = ['id' => $status_id];

        foreach ($changes as $key => $value) {
            $fields[] = "$key = :$key";
            $params[$key] = $value;
        }

        if (empty($fields)) {
            return;
        }

        $sql = "UPDATE status SET " . implode(', ', $fields) . " WHERE status_id = :id";

        $this->db->query()->run($sql, $params);
    }
}
// Redundant afte a recent refactor:
    /** Helper: Map status row to context object */
        // private function mapRowToStatus(array $row): StatusContext {
        //     $ctx                = new StatusContext($row);
        //     return $ctx;
        // }

    /** API: Get all `status` table data */
        // public function getAllStatuses(): array {
        //     $request = $this->db->query()->fetchAll("SELECT * FROM status");
        //     $out = [];

        //     foreach ($request as $status) {
        //         $out[] = $this->mapRowToStatus($status);
        //     }

        //     return $out;
        // }

    /** API: Get all `status`.`active` table data */
        // public function getAllActiveStatuses(): array {
        //     $request = $this->db->query()->fetchAll("SELECT * FROM status WHERE active=1");
        //     $out = [];

        //     foreach ($request as $row) {
        //         $out[] = StatusContext::fromRow($row);
        //     }

        //     return $out;
        // }

    /** API: Get a specific `status` raw row based on a id */
        // public function getStatusRowById(int $statusId): ?array {
        //     return $this->db->query()->fetchOne("SELECT * FROM status WHERE id = :id LIMIT 1", ['id' => $statusId]);
        // }
    
    /** API: Return all active status rows for a set of books */
        // public function getActiveStatus(array $bookIds): ?array {
        //     if (!$bookIds) return [];

        //     $sql = "
        //         SELECT bs.id, bs.book_id, bs.status_id, bs.active, bs.action_finished, s.type
        //         FROM book_status bs
        //         JOIN status s ON s.id = bs.status_id
        //         WHERE bs.book_id IN (" . implode(',', $bookIds) . ")
        //         AND bs.active = 1
        //     ";

        //     return $this->db->query()->fetchAll($sql);
        // }