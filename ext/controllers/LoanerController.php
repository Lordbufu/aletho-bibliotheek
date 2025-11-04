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

    /*  Handle AJAX request for loaners matching query. */
    public function requestLoaners() {
        $query = trim($_GET['query'] ?? '');
        header('Content-Type: application/json; charset=utf-8');

        if ($query === '' || mb_strlen($query) < 2) {
            echo json_encode([]);
            exit;
        }

        // Get matching loaners
        $loaners = $this->loaners->findByName($query);

        // Get all offices for display (id => name)
        $offices = $this->bookS->getOfficesForDisplay();
        $officeMap = [];
        foreach ($offices as $office) {
            $officeMap[$office['id']] = $office['name'];
        }

        // Build output with office name
        $out = [];
        foreach ($loaners as $l) {
            $out[] = [
                'name' => $l['name'],
                'email' => $l['email'],
                'location' => $officeMap[$l['office_id']] ?? ''
            ];
        }

        echo json_encode($out);
        exit;
    }

    /*  Handle AJAX request for loaner of a specific book. */
    public function requestLoanerForBook() {
        $bookId = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;
        $loaner = $this->loaners->getCurrentLoanerByBookId($bookId);

        header('Content-Type: application/json; charset=utf-8');
        
        if (!$loaner && $bookId < 1) {
            echo json_encode([]);
            exit;
        }

        // Optionally resolve office name
        $officeName = '';

        if (!empty($loaner['office_id'])) {
            $offices = $this->bookS->getOfficesForDisplay();
            foreach ($offices as $office) {
                if ($office['id'] == $loaner['office_id']) {
                    $officeName = $office['name'];
                    break;
                }
            }
        }

        echo json_encode([
            'name' => $loaner['name'] ?? '',
            'email' => $loaner['email'] ?? '',
            'location' => $officeName
        ]);
        exit;
    }
}