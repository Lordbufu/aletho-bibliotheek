<?php
namespace App\Engine\Instructions;

final class NotificationInstruction {
    // Required but can sometimes be null
    public ?int     $loanerId;
    public string   $type;
    // Optional
    public ?int     $originOfficeId = null;
}