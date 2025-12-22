<?php

namespace App\Libs;

/** Repository for managing genres and their many-to-many relation with books */
class GenreRepo {
    protected \App\Database $db;

    public function __construct(\App\Database $db) {
        $this->db = $db;
    }

    // New re-factored Helper functions
    /** Helper: Resolve an array of genre names or IDs into valid genre IDs */
    protected function _getOrCreateGenreIds(array $names): array {
        if (empty($names)) return [];

        $genres     = $this->getAllGenres(); 
        $nameToId   = array_column($genres, null, 'name');
        $genreIds   = [];

        foreach ($names as $name) {
            if (is_numeric($name)) {
                $genreIds[] = (int)$name;
                continue;
            }

            if (isset($nameToId[$name])) {
                $genre      = $nameToId[$name];
                $genreId    = (int)$genre['id'];

                if ((int)$genre['active'] === 0) {
                    $query  = "UPDATE genres SET active = 1 WHERE id = ?";
                    $this->db->query()->run($query, [$genreId]);
                }
            } else {
                $query      = "INSERT INTO genres (name, active) VALUES (?, 1)";
                $this->db->query()->run($query, [$name]);
                $genreId    = $this->db->query()->lastInsertId();
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

    /** API & Helper: Get all genres from the `genres` table */
    public function getAllGenres(): array {
        $query = "SELECT * FROM genres";
        return $this->db->query()->fetchAll($query);
    }

    /** Get genres for display */
    public function getGenresForDisplay(): array {
        $out = [];

        $genres = $this->getAllGenres();

        foreach ($genres as $genre) {
            if (!$genre['active']) {
                continue;
            }

            $out[] = $this->formatGenreForDisplay($genre);
        }

        return $out;
    }

    /** Get all genre names for a given book ID, uses a direct JOIN query for efficiency (avoids scanning all links) */
    public function getLinksByBookId(int $bookId) {
        return $this->db->query()->fetchAll(
            "SELECT genre_id FROM book_genre WHERE book_id = ?",
            [$bookId]
        );
    }

    /** Get all genre names for a given book ID */
    public function getGenreNamesByBookId(int $bookId): string {
        $rows = $this->db->query()->fetchAll(
            "SELECT g.name 
             FROM genres g 
             JOIN book_genre bg ON g.id = bg.genre_id 
             WHERE bg.book_id = ?",
            [$bookId]
        );

        return implode(', ', array_column($rows, 'name'));
    }

    /** Get genre by name to support input elements instead of select elements */
    public function getGenreByName(string $name): ?array {
        return $this->db->query()->fetchOne(
            "SELECT * FROM `genres` WHERE `name` = ?",
            [$name]
        );
    }

    // New re-factored API functions
    /** API: Ensure all `genre` & `book_genre` data is correct, and the table isnt getting polluted over time */
    public function syncBookGenres(int $bookId, array $names): void {
        $newIds         = $this->_getOrCreateGenreIds($names);
        $currentIds     = array_column($this->getLinksByBookId($bookId), 'genre_id');
        $toDelete       = array_diff($currentIds, $newIds);
        $toAdd          = array_diff($newIds, $currentIds);


        if (!empty($toDelete)) {
            $placeholders   = implode(',', array_fill(0, count($toDelete), '?'));
            $query          = "DELETE FROM book_genre WHERE book_id = ? AND genre_id IN ($placeholders)";
            $params         = array_merge([$bookId], $toDelete);

            $this->db->query()->run($query, $params);
        }

        if (!empty($toAdd)) {
            $placeholders   = implode(', ', array_fill(0, count($toAdd), '(?, ?)'));
            $values         = [];
            foreach ($toAdd as $gid) {
                $values[]   = $bookId;
                $values[]   = $gid;
            }

            $query          =  "INSERT INTO book_genre (book_id, genre_id) VALUES $placeholders";
            $this->db->query()->run($query, $values);
        }
    }
}

/** Old Obsolete functions, that have be replaced with better versions
    public function addBookGenres(int $bookId, array $names): void {
        if (empty($names)) return;

        $genreIds = $this->_getOrCreateGenreIds($names);
        $existingIds = array_column($this->getLinksByBookId($bookId), 'genre_id');

        $toAdd = array_diff($genreIds, $existingIds);
        if (!empty($toAdd)) {
            $placeholders = implode(', ', array_fill(0, count($toAdd), '(?, ?)'));
            $values = [];
            foreach ($toAdd as $gid) {
                $values[] = $bookId;
                $values[] = $gid;
            }
            $this->db->query()->run(
                "INSERT INTO book_genre (book_id, genre_id) VALUES $placeholders",
                $values
            );
        }
    }

    public function updateBookGenres(int $bookId, array $genres): void {
        $genreIds = $this->_getOrCreateGenreIds($genres);
        $currentIds = array_column($this->getLinksByBookId($bookId), 'genre_id');

        $toDelete = array_diff($currentIds, $genreIds);
        $toAdd = array_diff($genreIds, $currentIds);

        if (!empty($toDelete)) {
            $placeholders = implode(',', array_fill(0, count($toDelete), '?'));
            $this->db->query()->run(
                "DELETE FROM book_genre WHERE book_id = ? AND genre_id IN ($placeholders)",
                array_merge([$bookId], $toDelete)
            );
        }

        if (!empty($toAdd)) {
            $placeholders = implode(', ', array_fill(0, count($toAdd), '(?, ?)'));
            $values = [];
            foreach ($toAdd as $gid) {
                $values[] = $bookId;
                $values[] = $gid;
            }
            $this->db->query()->run(
                "INSERT INTO book_genre (book_id, genre_id) VALUES $placeholders",
                $values
            );
        }
    }
 */