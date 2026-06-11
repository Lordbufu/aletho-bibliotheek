<?php
namespace App\Libs;

use App\Libs\Context\GenreContext;

final class GenreRepo {
    private \App\Database $db;

    public function __construct() {
        $this->db = \App\App::getService('database');
    }

    // Re-factored but not tested yet
    /** API: Get all genre names and ids with mode filter */
    public function getAllGenres($mode = "all"): ?array {
        match ($mode) {
            "all" => $rows = $this->db->query()->fetchAll("SELECT * FROM genres"),
            "active" => $rows = $this->db->query()->fetchAll("SELECT * FROM genres WHERE is_active =1")
        };

        return $rows ? array_map(fn($r) => GenreContext::fromRow($r), $rows) : null;
    }

    // Re-factored but not tested yet
    /** API: Get all genre(s) from (a) book_id(s)
     *      @param int|int[]|null $book_ids
     *      @return string|array|null
     */
    public function getBookGenresByBookId(mixed $book_ids): mixed {
        if (!$book_ids) {
            return [];
        }

        $out = [];

        if (!is_array($book_ids)) {
            $book_id = (int)$book_ids;
            $rows = $this->db->query()->fetchAll("
                    SELECT g.genre_id, g.genre_name
                    FROM genre g
                    JOIN book_genres bg
                        ON bg.genre_id = g.genre_id
                    WHERE bg.book_id = :book_id
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
            SELECT g.genre_id, g.genre_name, bg.book_id
            FROM genres g
            JOIN book_genres bg
                ON bg.genre_id = g.genre_id
            WHERE bg.book_id IN (" . implode(',', $placeholders) . ")
            ORDER BY bg.book_id
        ";

        $rows = $this->db->query()->fetchAll($sql, $params);

        if (!$rows) {
            return [];
        }

        foreach ($rows as $r) {
            $bid = (int)$r['book_id'];
            $out[$bid][] = [
                'genre_id' => $r['genre_id'],
                'genre_name' => $r['genre_name']
            ];
        }

        return $out;
    }   

    // Review for the refactor
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

    // Review for the refactor
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
