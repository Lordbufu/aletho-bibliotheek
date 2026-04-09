<?php
namespace App\Engine;

use App\Libs\Context\{BookContext, BookStatusContext, LoanContext, LoanerContext, StatusContext};

final class TransitionContext {
    public BookContext          $book;
    public StatusContext        $newStatus;
    public BookStatusContext    $bookStatus;
    public ?LoanContext         $currentLoan        = null;
    public ?LoanerContext       $currentLoaner      = null;
    
    /** Extra context for office changes */
    public ?int                 $targetOfficeId     = null;
    /** Optional: the user who triggered the change (useful for logging) */
    public ?int                 $triggerUserId      = null;
}