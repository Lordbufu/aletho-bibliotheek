<?php
namespace App\Libs\Context;

/** `book_reservations` db table */
final class ReservationContext {
    public int                  $br_id;
    public int                  $book_id;
    public int                  $loaner_id;
    public int                  $loc_id;
    public \DateTimeImmutable   $created_at;
    public ?\DateTimeImmutable  $expires_at;
    public bool                 $is_active;

    /** Constructor for ease of use */
    public function __construct(array $row) {
        $this->br_id        = $row["br_id"];
        $this->book_id      = $row["book_id"];
        $this->loaner_id    = $row["loaner_id"];
        $this->loc_id       = $row["loc_id"];
        $this->created_at   = $row["created_at"];
        $this->expires_at   = $row["expires_at"] ?? null;
        $this->is_active    = $row["is_active"];
    }

    /** fromRow($row): To easily construct arrays of data */
    // public static function fromRow(array $row): self {}
}