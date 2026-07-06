<?php
namespace App\Services;

use App\Libs\GenreRepo;
use App\ViewModel\GenreViewModel;

// Re-factor status: Tested and working
final class GenreService {
    private GenreRepo $genre;

    public function __construct() {
        $this->genre = new \App\Libs\GenreRepo();
    }

    /** Facade: Get all genres stored in the database */
    public function getAllGenres(): ?array {
        return $this->genre->getAllGenres();
    }

    /** Facade: Get all active genres stored in the database */
    public function getAllActiveGenres(): ?array {
        return $this->genre->getAllGenres('active');
    }

    /** Facade: Get all genre(s) from (a) book_id(s)
     *      @param int|int[]|null $book_ids
     *      @return string|array|null
     */
    public function getBookGenresByBookId(mixed $book_ids): mixed {
        return $this->genre->getBookGenresByBookId($book_ids);
    }

    /** API: Get all active genres for viewmodel */
    public function getGenresForView(): array {
        return GenreViewModel::formatMany($this->getAllActiveGenres());
    }
}