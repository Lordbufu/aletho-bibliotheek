<?php
namespace App\Libs\Context;

/** `book_reservations` db table */
final class StatusTransitionContext {
    public int      $st_id;
    public int      $from_status;
    public int      $to_Status;
    public ?int     $noti_id        = null;
    public array    $expires_at;
    public bool     $is_active;

    /** Constructor for ease of use */
    public function __construct(array $row) {}

    /** fromRow($row): To easily construct arrays of data */
    // public static function fromRow(array $row): self {}
}