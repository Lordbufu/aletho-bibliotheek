<?php
namespace App\Libs;

use App\Libs\Context\WriterContext;

final class WriterRepo {
    private \App\Database $db;

    public function __construct() {
        $this->db = \App\App::getService('database');
    }

    // Re-factored and tested
    /** API: Get all writer names and ids with mode filter */
    public function getAllWriters($mode = "all"): ?array {
        match ($mode) {
            "all" => $rows = $this->db->query()->fetchAll("SELECT * FROM writers"),
            "active" => $rows = $this->db->query()->fetchAll("SELECT * FROM writers WHERE is_active =1")
        };

        return $rows ? array_map(fn($r) => WriterContext::fromRow($r), $rows) : null;
    }

    // Re-factored and tested
    /** API: Get all writer(s) from (a) book_id(s)
     *      @param int|int[]|null $book_ids
     *      @return string|array|null
     */
    public function getBookWritersByBookId(mixed $book_ids): mixed {
        if (!$book_ids) {
            return [];
        }

        $out = [];

        if (!is_array($book_ids)) {
            $book_id = (int)$book_ids;
            $rows = $this->db->query()->fetchAll("
                    SELECT w.writer_id, w.writer_name
                    FROM writers w
                    JOIN book_writers bw
                        ON bw.writer_id = w.writer_id
                    WHERE bw.book_id = :book_id
                ", ["book_id" => $book_id]
            );
        
            $out[$book_id][] = $rows;
            return $out;
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
            SELECT w.writer_id, w.writer_name, bw.book_id
            FROM writers w
            JOIN book_writers bw
                ON bw.writer_id = w.writer_id
            WHERE bw.book_id IN (" . implode(',', $placeholders) . ")
            ORDER BY bw.book_id
        ";

        $rows = $this->db->query()->fetchAll($sql, $params);

        if (!$rows) {
            return [];
        }

        foreach ($rows as $r) {
            $bid = (int)$r['book_id'];
            $out[$bid][] = [
                'writer_id' => $r['writer_id'],
                'writer_name' => $r['writer_name']
            ];
        }

        return $out;
    }

    // Re-factor status: W.I.P.
    /** API: Resolve the writer input, and return all ids to caller */
    public function resolveWriterIds(array $writers): array {
        $ids = [];

        foreach ($writers as $writer) {
            // Case 1: existing writer by ID
            if ($writer['id']) {
                $ids[] = (int)$writer['id'];
                continue;
            }

            // Case 2: lookup by name (case-insensitive)
            $row = $this->db->query()->fetchOne(
                "SELECT writer_id, is_active FROM writers WHERE LOWER(writer_name) = LOWER(:name)",
                ['name' => $writer['name']]
            );


            if ($row) {
                $id = (int)$row['writer_id'];

                // Case 2a: found but inactive → reactivate
                if (!(bool)$row['is_active']) {
                    $this->db->query()->run(
                        "UPDATE writers SET is_active = 1 WHERE writer_id = :id",
                        ['id' => $id]
                    );
                }

                $ids[] = $id;
                continue;
            }

            // Case 3: new writer → insert
            $this->db->query()->run(
                "INSERT INTO writers (writer_name) VALUES (:name)",
                ['name' => $writer['name']]
            );

            $ids[] = (int)$this->db->query()->lastInsertId();
        }

        return $ids;
    }

    // Re-factor status: W.I.P.
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