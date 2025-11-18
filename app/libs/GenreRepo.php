<?php

namespace App\Libs;

use App\App;

/** Repository for managing genres and their many-to-many relation with books.
 *  Design notes:
 *      - Caches genres in memory for efficiency.
 *      - Does NOT cache book_genre links globally (queries per book instead).
 *      - Provides both additive (`addBookGenres`) and replace (`updateBookGenres`) flows.
 */
class GenreRepo {
    protected ?array        $genres = null;
    protected \App\Database $db;

    public function __construct() {
        $this->db = App::getService('database');
    }

    /** Resolve an array of genre names or IDs into valid genre IDs.
     *      - If a name exists but is inactive, it is reactivated.
     *      - If a name does not exist, a new genre row is inserted.
     *      - Numeric values are treated as IDs directly.
     *      @param array<int,string|int> $names
     *      @return array<int,int>
     */
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

    /** Get all genres from the `genres` table, results are cached in memory for the lifetime of this object.
     *      @return array<int, array<string,mixed>>
     */
    public function getAllGenres(): array {
        if ($this->genres === null) {
            $this->genres = $this->db->query()->fetchAll("SELECT * FROM genres");
        }

        return $this->genres;
    }

    /** Get all genre names for a given book ID, uses a direct JOIN query for efficiency (avoids scanning all links).
     *      @param int $bookId
     *      @return string Comma-separated genre names.
     */
    public function getLinksByBookId(int $bookId) {
        return $this->db->query()->fetchAll(
            "SELECT genre_id FROM book_genre WHERE book_id = ?",
            [$bookId]
        );
    }

    /** Get all genre names for a given book ID.
     *      @param int $bookId
     *      @return string Comma-separated genre names
     */
    public function getGenreNamesByBookId(int $bookId): string {
        // More efficient: join instead of scanning all links
        $rows = $this->db->query()->fetchAll(
            "SELECT g.name 
             FROM genres g 
             JOIN book_genre bg ON g.id = bg.genre_id 
             WHERE bg.book_id = ?",
            [$bookId]
        );

        return implode(', ', array_column($rows, 'name'));
    }

    /** Get genre by name to support input elements instead of select elements.
     *      @param string $name
     *      @return array The row associated with the name.
     */
    public function getGenreByName(string $name): ?array {
        return $this->db->query()->fetchOne(
            "SELECT * FROM `genres` WHERE `name` = ?",
            [$name]
        );
    }

    /** Add genres to a book without removing existing ones, only inserts missing links; does not delete.
     *      @param array $names -> The sanitized $_POST['book_genres'] data.
     *      @param int $bookId  -> The index value of the newly added book in the `BookRepo`.
     */
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

    /** Replace the set of genres for a book with a new set, uses diffing to minimize DB churn (only deletes/insert changes)
     *      @param int $bookId      -> The book id that needs updating.
     *      @param array $genres    -> Array of genre names or id's.
     */
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