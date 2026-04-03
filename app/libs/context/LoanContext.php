<?php
namespace App\Libs\Context;

/** `book_loaners` table data */
final class LoanContext {
    public int                  $id;                // book_loaners.id
    public int                  $bookId;            // book_loaners.book_id
    public int                  $loanerId;          // book_loaners.loaner_id
    public int                  $statusId;          // book_loaners.status_id
    public ?\DateTimeImmutable  $startDate;         // book_loaners.start_date
    public ?\DateTimeImmutable  $endDate    = null; // book_loaners.end_date
    public ?bool                $active;            // book_loaners.active
}