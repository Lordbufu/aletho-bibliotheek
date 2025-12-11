<?php

namespace App\Libs;

/** Repository for managing genres and their many-to-many relation with books */
class GenreRepo {
    protected ?array        $genres = null;
    protected \App\Database $db;

    public function __construct(\App\Database $db) {
        $this->db = $db;
    }

    /** Resolve an array of genre names or IDs into valid genre IDs */
    private function _getOrCreateGenreIds(array $names): array {
        if (empty($names)) return [];

        $this->getAllGenres();
        $nameToId = array_column($this->genres, null, 'name');

        $genreIds = [];
        foreach ($names as $name) {
            if (is_numeric($name)) {
                $genreIds[] = (int)$name;
                continue;
            }

            if (isset($nameToId[$name])) {
                $genre = $nameToId[$name];
                $genreId = (int)$genre['id'];

                if ((int)($genre['active'] ?? 1) === 0) {
                    $this->db->query()->run("UPDATE genres SET active = 1 WHERE id = ?", [$genreId]);
                    $this->genres[array_search($genreId, array_column($this->genres, 'id'))]['active'] = 1;
                }
            } else {
                $this->db->query()->run("INSERT INTO genres (name, active) VALUES (?, 1)", [$name]);
                $genreId = $this->db->query()->lastInsertId();
                $newGenre = ['id' => $genreId, 'name' => $name, 'active' => 1];
                $this->genres[] = $newGenre;
                $nameToId[$name] = $newGenre;
            }

            $genreIds[] = $genreId;
        }

        return $genreIds;
    }

    /** Helper: Set global genres */
    protected function setGenres(): array {
        $query = "SELECT * FROM genres";
        $this->genres = $this->db->query()->fetchAll($query);
    }

    protected function formatGenreForDisplay($genre): array {
        return [
            'id' => $genre['id'],
            'name' => $genre['name']
        ];
    }

    /** Get all genres from the `genres` table, results are cached in memory for the lifetime of this object */
    public function getAllGenres(): array {
        if ($this->genres === null) {
            $this->setGenres();
        }

        return $this->genres;
    }

    /** Get genres for display */
    public function getGenresForDisplay(): array {
        $out = [];

        if ($this->genres === null) {
            $this->setGenres();
        }

        foreach ($this->genres as $genre) {
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

    /** Add genres to a book without removing existing ones, only inserts missing links; does not delete */
    public function addBookGenres(array $names, int $bookId): void {
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

    /** Replace the set of genres for a book with a new set, uses diffing to minimize DB churn (only deletes/insert changes) */
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
}