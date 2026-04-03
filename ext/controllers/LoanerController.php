<?php
namespace Ext\Controllers;

use App\App;

class LoanerController {
    /** XHR Request for: Filling in loaner suggestion lists */
    public function requestLoaners() {
        $query = trim($_GET['query'] ?? '');

        if ($query === '' || mb_strlen($query) < 2) {
            return App::json([]);
        }
        
        $loaners = App::getService('loaner')->searchLoaners($query);

        return App::json($loaners);
    }

    /** XHR Request for: Request loaner data for a specific book */
    public function requestLoanerForBook() {
        $bookId = (int)$_GET['book_id'];
        $loaner = App::getService('loaner')->getLoanerForBook($bookId);

        if (empty($loaner['name'])) {
            $bookCtx = App::getService('books')->findBookById($bookId);

            if ($bookCtx && $bookCtx->resvLoanerId !== null) {
                $loanerObj = App::getService('loaner')->getLoanerById($bookCtx->resvLoanerId);
                $officeName = App::getService('offices')->getOfficeName($loanerObj->officeId);

                return App::json([
                    'name'     => $loanerObj->name ?? '',
                    'email'    => $loanerObj->email ?? '',
                    'location' => $officeName ?? ''
                ]);
            }

            return App::json([
                'name'     => '',
                'email'    => '',
                'location' => ''
            ]);
        }

        return App::json([
            'name'     => $loaner['name'] ?? '',
            'email'    => $loaner['email'] ?? '',
            'location' => $loaner['location'] ?? ''
        ]);
    }
}