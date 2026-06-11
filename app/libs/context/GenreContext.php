<?php
namespace App\Libs\Context;

/** `genre` & `book_genres` db table */
final class GenreContext {
    public int      $genre_id;
    public string   $genre_name;
    public ?int     $book_id;
    public bool     $is_active;

    /** Constructor for ease of use */
    public function __construct(array $row) {
        $this->genre_id     = (int)$row['genre_id'];
        $this->genre_name   = $row['genre_name'];
        $this->book_id      = isset($row['book_id']) ? (int)$row['book_id'] : null;
        $this->is_active    = (bool) $row['is_active'];
    }

    /** fromRow($row): To easily construct arrays of data */
    public static function fromRow(array $row): self {
        return new self($row);
    }
}