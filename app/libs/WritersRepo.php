<?php
namespace App\Libs;

final class WritersRepo {
    private \App\Database $db;

    public function __construct() {
        $this->db = \App\App::getService('database');
    }

    /** API: Get all writer names and ids */
    public function getAllWriters(): array {
        $sql = "SELECT id, name FROM writers ORDER BY name ASC";
        return $this->db->query()->fetchAll($sql);
    }


    /** API: Ensure all writer names exist, return their IDs */
    public function ensureWritersExist(array $names): array {
        $ids = [];

        foreach ($names as $name) {
            $name = trim($name);
            if ($name === '') continue;

            $existing = $this->db->query()->fetchOne(
                "SELECT id FROM writers WHERE name = :name",
                ['name' => $name]
            );

            if ($existing) {
                $ids[] = (int)$existing['id'];
                continue;
            }

            $this->db->query()->run(
                "INSERT INTO writers (name) VALUES (:name)",
                ['name' => $name]
            );

            $ids[] = (int)$this->db->query()->lastInsertId();
        }

        return $ids;
    }

    /** API: Return writer names grouped by book_id */
    public function getWritersForBooks(array $bookIds): array {
        if (!$bookIds) return [];

        $sql = "
            SELECT bw.book_id, w.name
            FROM book_writers bw
            JOIN writers w ON w.id = bw.writer_id
            WHERE bw.book_id IN (" . implode(',', $bookIds) . ")
        ";

        $rows = $this->db->query()->fetchAll($sql);

        $map = [];
        foreach ($rows as $row) {
            $map[$row['book_id']][] = $row['name'];
        }

        return $map;
    }

    /** API: Replace all writers for a book */
    public function syncBookWriters(int $bookId, array $writerIds): void {
        $this->db->query()->run("DELETE FROM book_writers WHERE book_id = :id", ['id' => $bookId]);

        foreach ($writerIds as $wid) {
            $this->db->query()->run(
                "INSERT INTO book_writers (book_id, writer_id) VALUES (:b, :w)",
                ['b' => $bookId, 'w' => $wid]
            );
        }
    }
}
