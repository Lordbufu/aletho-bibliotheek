<?php
namespace App\Engine\Instructions;

final class LoanChangeInstruction {
    // 'book_loaners' table main context
    public ?int                 $bookId                 = null;
    public ?int                 $loanerId               = null;
    public ?int                 $statusId               = null;
    public ?\DateTimeImmutable  $startDate              = null;
    public ?\DateTimeImmutable  $endDate                = null;
    public bool                 $active                 = true;
    // Required for update triggers in the service layer
    public ?int                 $existingLoanRowId      = null;
    // Required for flow specific deactive loan triggers
    public ?int                 $recyleLoanRowId        = null;
}