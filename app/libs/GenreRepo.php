<?php
namespace App\Libs;

use App\App;

/**
 * 
 */
class GenreRepo {
    protected array $genres;
    protected array $links;

    /**
     * 
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

    /**
     * 
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

    /**
     * 
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
}