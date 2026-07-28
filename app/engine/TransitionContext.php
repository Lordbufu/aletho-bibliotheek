<?php
namespace App\Engine;

use App\Libs\Context\{BookContext, BookStatusContext, ReservationContext, LoanerContext};

final class TransitionContext {
    public BookContext              $book;
    public BookStatusContext        $bookStatus;
    public int                      $reqStatusId;
    public LoanerContext|null       $loaner;
    public ReservationContext|null  $reservation;
}