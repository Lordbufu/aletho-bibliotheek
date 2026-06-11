<?php
namespace App\Libs\Context;

/** Merged `loaners` & `book_loaners` db table */
final class LoanerContext {
    // Loaners:
    public int      $loaner_id;
    public string   $loaner_name;
    public string   $loaner_email;
    public int      $loaner_locId;
    public bool     $is_active;
    // Book Loaners:
    public ?int     $bl_id          = null;
    public ?int     $book_id        = null;
    public ?int     $status_id      = null;
    public ?string  $start_at       = null;
    public ?string  $end_at         = null;
    public ?bool    $bl_is_active   = null;

    /** Constructor for ease of use */
    public function __construct(?array $row) {
        $this->loaner_id    = $row['loaner_id'];
        $this->loaner_name  = $row['loaner_name'];
        $this->loaner_email = $row['loaner_email'];
        $this->loaner_locId = $row['loaner_locId'];
        $this->is_active    = $row['is_active'];

        $this->bl_id        = isset($row['bl_id']) ? $row['bl_id'] : null;
        $this->book_id      = isset($row['book_id']) ? $row['book_id'] : null;
        $this->status_id    = isset($row['status_id']) ? $row['status_id'] : null;
        $this->start_at     = isset($row['start_at']) ? $row['start_at'] : null;
        $this->end_at       = isset($row['end_at']) ? $row['end_at'] : null;
        $this->bl_is_active = isset($row['is_active']) ? $row['is_active'] : null;
    }

    /** fromRow($row): To easily construct arrays of data */
    public static function fromRow(array $row): self {
        return new self($row);
    }
}