<?php
namespace Ext\Controllers;

// TODO: Consider adjusting the view model for loaner data, and introducing a request email function, to reduce the risk of exposing all emails.
// And also make sure to discuss this with the client, as this might mean that it takes a week or two longer then expected.
// Re-factor status: tested and working
class LoanerController {
    private \App\App                                    $app;
    
    public function __construct() {
        $this->app          = new \App\App();
    }

    /** XHR Request for: Filling in loaner suggestion lists */
    public function requestLoaners() {
        $this->app::getService('auth')->requireRole(['office_admin', 'global_admin']);

        $query              = trim($_GET['query'] ?? '');

        if ($query === '' || mb_strlen($query) < 2) {
            return $this->app::json([]);
        }
        
        $loaners            = $this->app::getService('loaner')->searchLoaners($query);

        return $this->app::json($loaners);
    }

    /** XHR Request for: Request loaner data for a specific book */
    public function requestLoanerForBook() {
        $this->app::getService('auth')->requireRole(['office_admin', 'global_admin']);

        $bookId             = (int)$_GET['book_id'];
        $loaner             = $this->app::getService('loaner')->getLoanerForBook($bookId) ?? null;

        return $this->app::json($loaner);
    }
}