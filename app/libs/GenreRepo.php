<?php

namespace App\Libs;

class GenreRepo {
    protected \App\Database $db;
    protected ?array        $cachedGenres = null;

    public function __construct(\App\Database $db) {
        $this->db = $db;
    }

    /** Helper: Resolve an array of genre names or IDs into valid genre IDs */
    protected function _getOrCreateGenreIds(array $names): array {
        if (empty($names)) return [];

        $nameToId   = array_column($this->getAllGenres(), null, 'name');
        $genreIds   = [];

        /** Process each provided name */
        foreach ($names as $name) {
            /** If numeric, assume it's already a genre ID */
            if (is_numeric($name)) {
                $genreIds[] = (int)$name;
                continue;
            }

            /** Reactivate existing genre or create new genre */
            if (isset($nameToId[$name])) {
                $genre      = $nameToId[$name];
                $genreId    = (int)$genre['id'];

                if ((int)$genre['active'] === 0) {
                    $query  = "UPDATE genres SET active = 1 WHERE id = ?";
                    $this->db->query()->run($query, [$genreId]);
                    $this->cachedGenres = null;
                }
            } else {
                $query      = "INSERT INTO genres (name, active) VALUES (?, 1)";
                $this->db->query()->run($query, [$name]);
                $genreId    = $this->db->query()->lastInsertId();
                $this->cachedGenres = null;
            }

            $genreIds[]     = $genreId;
        }

        return $genreIds;
    }

    /** Helper: Strip irrelevant data for frontend presentation */
    protected function formatGenreForDisplay($genre): array {
        return [
            'id' => $genre['id'],
            'name' => $genre['name']
        ];
    }

    /** Helper: Get all genre IDs for a given book ID */
    protected function getGenreIdsByBookId(int $bookId): array {
        $rows = $this->db->query()->fetchAll(
            "SELECT genre_id FROM book_genre WHERE book_id = ?",
            [$bookId]
        );

        return array_column($rows, 'genre_id');
    }

    /** Helper: Get genre names for a given book ID */
    protected function getGenreNameListByBookId(int $bookId): array {
        $rows = $this->db->query()->fetchAll(
            "SELECT g.name 
            FROM genres g 
            JOIN book_genre bg ON g.id = bg.genre_id 
            WHERE bg.book_id = ?",
            [$bookId]
        );

        return array_column($rows, 'name');
    }

    /** API & Helper: Get all genres from the `genres` table */
    public function getAllGenres(): array {
        /** Return cached genres if available */
        if ($this->cachedGenres !== null) {
             return $this->cachedGenres;
        }

        /** Fetch all genres, cache the results and return said results */
        $query = "SELECT * FROM genres";
        $this->cachedGenres = $this->db->query()->fetchAll($query);
        return $this->cachedGenres;
    }

    /** API: Get genres for display */
    public function getGenresForDisplay(): array {
        return array_map(
            fn($g) => $this->formatGenreForDisplay($g),
            array_filter(
                $this->getAllGenres(),
                fn($g) => $g['active']
            )
        );
    }

    /** API: Get all genre names for a given book ID */
    public function getGenreNamesByBookId(int $bookId): string {
        $names = $this->getGenreNameListByBookId($bookId);
        return implode(', ', $names);
    }

    /** API: Ensure all `genre` & `book_genre` data is correct, and the table isnt getting polluted over time */
    public function syncBookGenres(int $bookId, array $names): void {
        $newIds         = $this->_getOrCreateGenreIds($names);
        $currentIds     = array_column($this->getGenreIdsByBookId($bookId), 'genre_id');
        $removeIds      = array_diff($currentIds, $newIds);
        $addIds         = array_diff($newIds, $currentIds);

        /** Remove any genre associations that are no longer needed */
        if (!empty($removeIds)) {
            $placeholders   = implode(',', array_fill(0, count($removeIds), '?'));
            $query          = "DELETE FROM book_genre WHERE book_id = ? AND genre_id IN ($placeholders)";
            $params         = array_merge([$bookId], $removeIds);

            $this->db->query()->run($query, $params);
        }

        /** Add any new genre associations */
        if (!empty($addIds)) {
            $placeholders   = implode(', ', array_fill(0, count($addIds), '(?, ?)'));
            $values         = [];
            foreach ($addIds as $gid) {
                $values[]   = $bookId;
                $values[]   = $gid;
            }

            $query          =  "INSERT INTO book_genre (book_id, genre_id) VALUES $placeholders";
            $this->db->query()->run($query, $values);
        }
    }

    // Potentially useless function?
    /** API: Get genre by name to support input elements instead of select elements */
    public function getGenreByName(string $name): ?array {
        return $this->db->query()->fetchOne(
            "SELECT * FROM `genres` WHERE `name` = ?",
            [$name]
        );
    }
}