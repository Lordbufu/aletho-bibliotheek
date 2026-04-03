<?php
namespace App\Libs\Context;

/** Basically just `loaners` table properties */
final class LoanerContext {
    public int      $id;        // loaners.id
    public string   $name;      // loaners.name
    public string   $email;     // loaners.email
    public int      $officeId;  // loaners.office_id
    public bool     $active;    // loaners.active
}