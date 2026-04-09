<?php
namespace App\Services;

use App\App;
use App\Libs\Context\LoanerContext;
use App\Libs\{LoanerRepo, OfficesRepo};

final class LoanerService {
    private LoanerRepo  $loaner;
    private OfficesRepo $offices;

    public function __construct() {
        $this->loaner   = new LoanerRepo();
        $this->offices  = new OfficesRepo();
    }

    /** Facade: Get loaner by id */
    public function getLoanerById(int $loanerId): ?LoanerContext {
        return $this->loaner->getLoanerById($loanerId);
    }

    /** Facade: Search loaners based on variable query string */
    public function findLoanerByName(string $query): array {
        return $this->loaner->findLoanerByName($query);
    }

    /** Facade: Find loaner or create if non is found */
    public function findOrCreateLoaner(string $name, string $email, string $location): int {
        return $this->loaner->findOrCreateLoaner($name, $email, $location);
    }

    /** API: Provide data context for frontend XHR requests */
    public function getLoanerForBook($bookId): array {
        $bookStatusCtx      = App::getService('book_status')->loadBookStatusContext($bookId);
        $loanCtx            = App::getService('loan')->getCurrentLoanById($bookStatusCtx->status['id'], $bookStatusCtx->book['id']);

        // No loan? Return empty structure immediately
        if (!$loanCtx || !$loanCtx->loanerId) {
            return [
                'name'      => '',
                'email'     => '',
                'location'  => ''
            ];
        }

        $loaner             = $this->loaner->getLoanerById($loanCtx->loanerId);

        // No loaner found? Also return empty structure
        if (!$loaner) {
            return [
                'name'      => '',
                'email'     => '',
                'location'  => ''
            ];
        }

        $officeLocation     = App::getService('offices')->getOfficeName($loaner->officeId);

        return [
            'name'          => $loaner->name ?? '',
            'email'         => $loaner->email ?? '',
            'location'      => $officeLocation ?? ''
        ];
    }

    /** API: Search loaners based on variable query string for frontend XHR requests */
    public function searchLoaners(string $query): ?array {
        $loanersCtx = $this->loaner->findLoanerByName($query);
        $loaners = [];

        foreach ($loanersCtx as $loaner) {
            $loaners[] = [
                'name' => $loaner->name,
                'email' => $loaner->email,
                'location' => $this->offices->getOfficeName($loaner->officeId) ?? '',
                'office_id' => $loaner->officeId
            ];
        }
        
        return $loaners;
    }
}