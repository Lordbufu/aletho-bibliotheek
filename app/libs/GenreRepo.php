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

        return $this->links;
    }

    /**
     * 
     */
    public function getGenreNamesByBookId(int $bookId): array {
        $mapNames = array_column($this->getAllGenres(), 'name', 'id');
        $names = [];
        
        foreach ($this->getAllLinks() as $link) {
            if ((int)$link['book_id'] === $bookId) {
                $names[] = $mapNames[$link['genre_id']] ?? 'Unknown';
            }
        }

        return $names;
    }
}