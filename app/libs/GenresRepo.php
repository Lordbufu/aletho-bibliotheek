<?php
namespace App\Libs;

final class GenresRepo {
    private \App\Database $db;

    public function __construct() {
        $this->db = \App\App::getService('database');
    }

    /** API: Get all genre names and ids */
    public function getAllGenres(): array {
        $sql = "SELECT id, name FROM genres ORDER BY name ASC";
        return $this->db->query()->fetchAll($sql);
    }

    /** API: Ensure all genre names exist, return their IDs */
    public function ensureGenresExist(array $names): array {
        $ids = [];

        foreach ($names as $name) {
            $name = trim($name);
            if ($name === '') continue;

            $existing = $this->db->query()->fetchOne(
                "SELECT id FROM genres WHERE name = :name",
                ['name' => $name]
            );

            if ($existing) {
                $ids[] = (int)$existing['id'];
                continue;
            }

            $this->db->query()->run(
                "INSERT INTO genres (name) VALUES (:name)",
                ['name' => $name]
            );

            $ids[] = (int)$this->db->query()->lastInsertId();
        }

        return $ids;
    }

    /** API: Return genre names grouped by book_id */
    public function getGenresForBooks(array $bookIds): array {
        if (!$bookIds) return [];

        $sql = "
            SELECT bg.book_id, g.name
            FROM book_genre bg
            JOIN genres g ON g.id = bg.genre_id
            WHERE bg.book_id IN (" . implode(',', $bookIds) . ")
        ";

        $rows = $this->db->query()->fetchAll($sql);

        $map = [];
        foreach ($rows as $row) {
            $map[$row['book_id']][] = $row['name'];
        }

        return $map;
    }

    /** API: Replace all genres for a book */
    public function syncBookGenres(int $bookId, array $genreIds): void {
        $this->db->query()->run("DELETE FROM book_genre WHERE book_id = :id", ['id' => $bookId]);

        foreach ($genreIds as $gid) {
            $this->db->query()->run(
                "INSERT INTO book_genre (book_id, genre_id) VALUES (:b, :g)",
                ['b' => $bookId, 'g' => $gid]
            );
        }
    }
}
