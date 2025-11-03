<?php
namespace Ext\Controllers;

use App\{App, BooksService, LoanersService, ValidationService};

class LoanerController {
    protected BooksService      $bookS;
    protected LoanersService    $loaners;
    protected ValidationService $valS;

    /*  Construct App services as default local service. */
    public function __construct() {
        try {
            $this->bookS    = App::getService('books');
            $this->loaners  = App::getService('loaners');
            $this->valS     = App::getService('val');
        } catch (\Throwable $t) {
            throw $t;
        }
    }

    /*  */
    public function requestLoaners() {
        dd('W.I.P.');
    }    /*  */
}