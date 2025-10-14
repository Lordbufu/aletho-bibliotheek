<?php

namespace App\Libs;

use App\{App, Database};

/* Genres library, dealing with all genres table data & relations. */
class GenreRepo {
    protected array $genres;
    protected array $links;
    protected Database $db;

    public function __construct($con = []) {
        if  (!empty($con)) {
            $this->db = $con;
        }
    }

    /** Get all genres as defined in the `genres` table.
     *      @return array   -> All genres in the database.
     */
    public function getAllGenres(): array {
        if (!isset($this->genres)) {
            $this->genres = $this->db->query()->fetchAll("SELECT * FROM genres");
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
     *      @return array   -> All genre links in the database.
     */
    public function getAllLinks(): array {
        if (!isset($this->links)) {
            $this->links = $this->db->query()->fetchAll("SELECT * FROM book_genre");
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
     *      @param int $bookId  -> The id we want to know the genres for.
     *      @return string      -> Comma-separated genre names
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

    /** Get genre by name to support input elements instead of select elements.
     *      @param string $name -> Sanitized user input.
     *      @return array       -> The row associated with the name.
     */
    public function getGenreByName(string $name): ?array {
        return $this->db->query()->fetchOne(
            "SELECT * FROM `genres` WHERE `name` = ?",
            [$name]
        );
    }

    /** Get links by book_id.
     *      @param int $bookId  -> The book id as known in the Database.
     *      @return array       -> All associated links.
     */
    public function getLinksByBookId(int $bookId) {
        return $this->db->query()->fetchAll(
            "SELECT genre_id FROM book_genre WHERE book_id = ?",
            [$bookId]
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
                    $this->db->query()->run(
                        "UPDATE genres SET active = 1 WHERE id = ?",
                        [$genreId]
                    );

                    $nameToId[$name]['active'] = 1;
                }
            } else {
                // Insert new genre
                $this->db->query()->run(
                    "INSERT INTO genres (name, active) VALUES (?, 1)",
                    [$name]
                );

                $genreId = $this->db->query()->lastInsertId();

                // Update local cache
                $nameToId[$name] = [
                    'id' => $genreId,
                    'active' => 1
                ];

                $this->genres[] = [
                    'id' => $genreId,
                    'name' => $name,
                    'active' => 1
                ];
            }

            $genreIds[] = $genreId;
        }

        // Now handle links
        $existingLinks = $this->getLinksByBookId($bookId);
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

    /** Update the genres for a given book (many-to-many.
     *      @param int $bookId      -> The book id that needs updating.
     *      @param array $genres    -> Array of genre names or id's.
     *      @return void
     */
    public function updateBookGenres(int $bookId, array $genres): void {
        if (empty($genres)) {
            // If no genres passed, remove all links for this book
            $this->db->query()->run(
                "DELETE FROM book_genre WHERE book_id = ?",
                [$bookId]
            );
            return;
        }

        if (!isset($this->genres)) {
            $this->getAllGenres();
        }

        // Map: name => [id, active]
        $nameToId = [];
        foreach ($this->genres as $g) {
            $nameToId[$g['name']] = [
                'id'     => (int)$g['id'],
                'active' => (int)($g['active'] ?? 1)
            ];
        }

        $genreIds = [];
        foreach ($genres as $genre) {
            if (is_numeric($genre)) {
                $genreId = (int)$genre;
            } else {
                if (isset($nameToId[$genre])) {
                    $genreId = $nameToId[$genre]['id'];

                    // Reactivate if inactive
                    if ($nameToId[$genre]['active'] === 0) {
                        $this->db->query()->run(
                            "UPDATE genres SET active = 1 WHERE id = ?",
                            [$genreId]
                        );
                        $nameToId[$genre]['active'] = 1;
                    }
                } else {
                    // Insert new genre
                    $this->db->query()->run(
                        "INSERT INTO genres (name, active) VALUES (?, 1)",
                        [$genre]
                    );
                    $genreId = $this->db->query()->lastInsertId();

                    $nameToId[$genre] = ['id' => $genreId, 'active' => 1];
                    $this->genres[]   = ['id' => $genreId, 'name' => $genre, 'active' => 1];
                }
            }

            $genreIds[] = $genreId;
        }

        // Replace semantics: wipe old links, insert new ones
        $this->db->query()->run(
            "DELETE FROM book_genre WHERE book_id = ?",
            [$bookId]
        );

        foreach ($genreIds as $gid) {
            $this->db->query()->run(
                "INSERT INTO book_genre (book_id, genre_id) VALUES (?, ?)",
                [$bookId, $gid]
            );
        }
    }
}