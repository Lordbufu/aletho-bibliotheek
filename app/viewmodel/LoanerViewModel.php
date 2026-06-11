<?php
namespace App\ViewModel;

use App\Libs\Context\{LoanerContext, LocationContext};

final class LoanerViewModel {
    public int      $id;        // PK for `loaners`.`loaner_id`
    public string   $naam;      // Derived from `loaners`.`loaner_name`
    public string   $email;     // Derived from `loaners`.`loaner_email`
    public string   $locatie;   // Derived from 'locations'.`loc_name` via `loaners`.`loaner_locId`

    public function __construct(LoanerContext $lener, LocationContext $locatie) {
        $this->id       = $lener->loaner_id;
        $this->naam     = $lener->loaner_name;
        $this->email    = $lener->loaner_email;
        $this->locatie  = $locatie->loc_name;
    }

    public static function formatOne(array $lener, array $locatie): self {
        return new self($lener, $locatie);
    }

    /** Re-factor status: W.I.P. ... needs proper repo/service calls to provide the correct datasets ? */
    public static function formatMany(array $loaners, array $locaties): array {
        $formatted = [];

        foreach ($loaners as $loaner) {
            $format = [
                'id'        => $loaner->loaner_id,
                'naam'      => $loaner->loaner_name,
                'email'     => $loaner->loaner_email,
                'locatie'   => $locatie[$loaner->loaner_id]
            ];

            $formatted[] = new self($format);
        }

        return $formatted;
    }
}