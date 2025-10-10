<?php

namespace App\Libs;

use App\{App, Database};

/* Genres library, dealing with all genres table data & relations. */
class GenreRepo {
    protected array $genres;
    protected array $links;
    protected Database $db;

    public function __construct() {
        $this->db = App::getService('database');
    }

    /** Get all genres as defined in the `genres` table.
     *      @return array
     */
    public function getAllGenres(): array {
        if (!isset($this->genres)) {
            $this->genres = App::getService('database')
                ->query()
                ->fetchAll("SELECT * FROM genres");
        }

        if (!is_array($this->genres) || $this->genres === []) {
            App::getService('logger')->error(
                "The 'GenreRepo' dint get any genres from the database",
                'bookservice'
            );
        }

        return $this->genres;
    }

    /** Get all book_genre link table data (many-to-many relations).
     *      @return array
     */
    public function getAllLinks(): array {
        if (!isset($this->links)) {
            $this->links = App::getService('database')
                ->query()
                ->fetchAll("SELECT * FROM book_genre");
        }

        if (!is_array($this->links) || $this->links === []) {
            App::getService('logger')->error(
                "The 'GenreRepo' dint get any genre-links from the database",
                'bookservice'
            );
        }

        return $this->links;
    }

    /** Get all genre names for a given book ID.
     *      @param int $bookId
     *      @return string Comma-separated genre names
     */
    public function getGenreNamesByBookId(int $bookId): string {
        $mapNames = array_column($this->getAllGenres(), 'name', 'id');
        $names = [];
        
        foreach ($this->getAllLinks() as $link) {
            if ((int)$link['book_id'] !== $bookId) {
                continue;
            }

            $names[] = $mapNames[$link['genre_id']] ?? 'Unknown';
        }

        return implode(', ', $names);
    }

    /* Get a specific genre, return false if not found. */
    public function getGenreByName(string $name): ?array {
        return App::getService('database')->query()->fetchOne(
            "SELECT * FROM `genres` WHERE `name` = ?",
            [$name]
        );
    }

    /** Add book genres to the database, or activate incase already there.
     *  The function will also add new genres if not in the database.
     *      @param array $names -> The sanitized $_POST['book_genres'] data.
     *      @param int $bookId  -> The index value of the newly added book in the `BookRepo`.
     */
    public function addBookGenres(array $names, int $bookId): void {        
        if (empty($names)) {
            return;
        }

        if (!isset($this->genres)) {
            $this->getAllGenres();
        }

        // Map: name => [id, active]
        $nameToId = [];
        foreach ($this->genres as $genre) {
            $nameToId[$genre['name']] = [
                'id'     => (int)$genre['id'],
                'active' => (int)($genre['active'] ?? 1)
            ];
        }

        $genreIds = [];
        foreach ($names as $name) {
            if (isset($nameToId[$name])) {
                $genreId = $nameToId[$name]['id'];

                // Reactivate if inactive
                if ($nameToId[$name]['active'] === 0) {
                    $this->db->query()->run("UPDATE genres SET active = 1 WHERE id = ?", [$genreId]);
                    $nameToId[$name]['active'] = 1;
                }
            } else {
                // Insert new genre
                $this->db->query()->run("INSERT INTO genres (name, active) VALUES (?, 1)", [$name]);
                $genreId = $this->db->query()->lastInsertId();

                // Update local cache
                $nameToId[$name] = ['id' => $genreId, 'active' => 1];
                $this->genres[] = ['id' => $genreId, 'name' => $name, 'active' => 1];
            }

            $genreIds[] = $genreId;
        }

        // Now handle links
        $existingLinks = $this->db->query()->fetchAll(
            "SELECT genre_id FROM book_genre WHERE book_id = ?",
            [$bookId]
        );
        $existingIds = array_column($existingLinks, 'genre_id');

        foreach ($genreIds as $gid) {
            if (!in_array($gid, $existingIds, true)) {
                $this->db->query()->run(
                    "INSERT INTO book_genre (book_id, genre_id) VALUES (?, ?)",
                    [$bookId, $gid]
                );
            }
        }
    }

    /** Update the genres for a given book (many-to-many), removing any current links.
     *      @param int $bookId
     *      @param array $genres Array of genre names or IDs
     *      @return void
     */
    public function updateBookGenres(int $bookId, array $genres): void {
        // Make sure we have `local` genres
        if (empty($genres)) {
            return;
        }

        // Make sure all `global` genres are set
        if (!isset($this->genres)) {
            $this->getAllGenres();
        }

        // Map genre names to IDs if needed
        $nameToId = array_column($this->genres, 'id', 'name');

        foreach ($genres as $genre) {
            if (is_numeric($genre)) {
                $genreId = $genre;
            } else {
                // Check if genre exists, else insert
                if (isset($nameToId[$genre])) {
                    $genreId = $nameToId[$genre];
                } else {
                    App::getService('database')
                        ->query()
                        ->run("INSERT INTO genres (name) VALUES (?)", [$genre]);

                    $genreId = App::getService('database')
                        ->query()
                        ->lastInsertId();
                        
                    $nameToId[$genre] = $genreId; // update map for next loop
                }
            }

            App::getService('database')
                ->query()
                ->run(
                    "INSERT INTO book_genre (book_id, genre_id) VALUES (?, ?)",
                    [$bookId, $genreId]
            );
        }
    }
}