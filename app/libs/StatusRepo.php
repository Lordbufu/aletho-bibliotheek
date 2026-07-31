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