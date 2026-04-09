<?php
namespace App\Engine\Result;

use App\Engine\Instructions\{StatusChangeInstruction, LoanChangeInstruction, NotificationInstruction, OfficeChangeInstruction};

final class TransitionResult {
    public ?StatusChangeInstruction $statusChanges          = null;
    public ?LoanChangeInstruction   $loanChanges            = null;
    public ?NotificationInstruction $notifications          = null;
    public ?OfficeChangeInstruction $officeChanges          = null;
    public string                   $userFeedbackMessage;

    // Extra enriched context:
    public bool                     $passed                 = true;
    public ?string                  $errorMessage           = null;
    public ?string                  $loanerName             = '';
    public ?string                  $loanerEmail            = '';
    public ?string                  $loanerLocation         = '';
    public string                   $bookTitle              = '';
    public ?\DateTimeImmutable      $dueDate                = null;
}