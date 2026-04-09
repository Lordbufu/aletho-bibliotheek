<?php
namespace App\Engine\Instructions;

final class StatusChangeInstruction {
    public ?int                 $existingBookStatusId       = null;
    public ?int                 $reservationLoanerId        = null;
    public ?int                 $reservationLoanerOfficeId  = null;
    public string               $newStatusType;
    public bool                 $active                     = true;
    public ?string              $actionToken                = null;
    public ?\DateTimeImmutable  $tokenExpires               = null;
    // public ?\DateTimeImmutable  $dueDate                    = null;
    public ?bool                $reminderSend               = null;
}