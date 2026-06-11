<?php
namespace App\Libs\Context;

/** `books` db table */
final class BookContext {
    public int      $book_id;
    public string   $book_title;
    public int      $book_home_loc;
    public int      $book_cur_loc;
    public bool     $is_active;

    /** Constructor for ease of use */
    public function __construct(array $row) {
        $this->book_id          = (int)$row['book_id'];
        $this->book_title       = $row['book_title'];
        $this->book_home_loc    = (int)$row['book_home_loc'];
        $this->book_cur_loc     = (int)$row['book_cur_loc'];
        $this->is_active        = (bool)$row['is_active'];
    }

    /** fromRow($row): To easily construct arrays of data */
    public static function fromRow(array $row): self {
        return new self($row);
    }
}