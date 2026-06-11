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
    public function __construct(array $row) {}

    /** fromRow($row): To easily construct arrays of data */
    // public static function fromRow(array $row): self {}
}