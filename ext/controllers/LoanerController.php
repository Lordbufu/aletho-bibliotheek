<?php
namespace Ext\Controllers;

// Re-factor status: tested and working
class LoanerController {
    private \App\App                                    $app;
    
    public function __construct() {
        $this->app          = new \App\App();
    }

    /** XHR Request for: Filling in loaner suggestion lists */
    public function requestLoaners() {
        $query = trim($_GET['query'] ?? '');

        if ($query === '' || mb_strlen($query) < 2) {
            return $this->app::json([]);
        }
        
        $loaners = $this->app::getService('loaner')->searchLoaners($query);

        return $this->app::json($loaners);
    }

    /** XHR Request for: Request loaner data for a specific book */
    public function requestLoanerForBook() {
        $bookId = (int)$_GET['book_id'];
        $loaner = $this->app::getService('loaner')->getLoanerForBook($bookId) ?? null;

        return $this->app::json($loaner);
    }
}