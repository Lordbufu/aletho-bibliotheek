<?php
namespace App\Libs\Context;

/** `writers` & `book_writers` db table */
final class WriterContext {
    public int      $writer_id;
    public string   $writer_name;
    public ?int     $book_id;
    public bool     $is_active;

    /** Constructor for ease of use */
    public function __construct(array $row) {
        $this->writer_id    = (int)$row['writer_id'];
        $this->writer_name  = $row['writer_name'];
        $this->book_id      = isset($row['book_id']) ? (int)$row['book_id'] : null;
        $this->is_active    = (bool) $row['is_active'];
    }

    /** fromRow($row): To easily construct arrays of data */
    public static function fromRow(array $row): self {
        return new self($row);
    }
}