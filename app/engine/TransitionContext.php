<?php
namespace App\Engine;

use App\Libs\Context\{BookContext, BookStatusContext, ReservationContext, LoanerContext};

final class TransitionContext {
    public BookContext          $book;
    public BookStatusContext    $bookStatus;
    public int                  $reqStatusId;
    public ?LoanerContext       $loaner;
    public ?ReservationContext  $reservation;

    public function __construct(BookContext $book, BookStatusContext $bookStatus, int $reqStatusId, ?LoanerContext $loaner, ?ReservationContext $reservation) {
        $this->book         = $book;
        $this->bookStatus   = $bookStatus;
        $this->reqStatusId  = $reqStatusId;
        $this->loaner       = $loaner;
        $this->reservation  = $reservation;
    }
}