<?php
namespace App\Services;

use App\App;
use App\Libs\{LoanerRepo, LocationRepo};
use App\ViewModel\LoanerViewModel;

final class LoanerService {
    private LoanerRepo   $loaner;
    private LocationRepo $location;

    public function __construct() {
        $this->loaner   = new LoanerRepo();
        $this->location  = new LocationRepo();
    }

    /** Re-factored/new functions */
    // Re-factor status: tested and working (maybe not usefull ?)
    public function getLoanersForView() {
        return $this->loaner->getAllActiveLoaners();
    }

    // Re-factor status: tested and working
    /** API: Provide singleton data context for frontend XHR requests */
    public function getLoanerForBook($bookId): LoanerViewModel {
        $bookLoaner = $this->loaner->getActiveLoanerByBookId($bookId);
        $location   = $this->location->getLocationContextById($bookLoaner->loaner_locId);

        return new LoanerViewModel($bookLoaner, $location);
    }

    /** API: Search loaners based on variable query string for frontend XHR requests */
    public function searchLoaners(string $query): ?array {
        $loanersCtx = $this->loaner->findLoanerByName($query);
        $loaners    = [];

        foreach ($loanersCtx as $loaner) {
            $loaners[]  = new LoanerViewModel(
                $loaner,
                $this->location->getLocationContextById($loaner->loaner_id)
            );
        }

        return $loaners;
    }

    // /** Facade: Create new loaner loaner and return its id */
    // public function createLoaner(string $name, string $email, int $officeId): int {
    //     return $this->loaner->createLoaner($name, $email, $officeId);
    // }

    // /** Facade: Reactive loaner based on id */
    // public function reactivateLoaner(int $id): void {
    //     $this->loaner->reactivateLoaner($id);
    // }

    // /** Facade: Get loaner by id */
    // public function getLoanerById(int $loanerId): ?LoanerContext {
    //     return $this->loaner->getLoanerById($loanerId);
    // }

    // /** Facade: Search loaners based on variable query string */
    // public function findLoanerByName(string $query): array {
    //     return $this->loaner->findLoanerByName($query);
    // }

    // /** Facade: Find loaner by name and email, returns null if not found */
    // public function findLoanerByExactName(string $name): ?array {
    //     return $this->loaner->findLoanerByExactName($name);
    // }

    // /** API: Attempt to find or create a loaner */
    // public function findOrCreateLoaner(string $name, string $email, int $officeId): int {
    //     $tempLoaner = $this->loaner->findLoanerByExactName($name);

    //     if ($tempLoaner) {
    //         if (!$tempLoaner['active']) {
    //             $this->loaner->reactivateLoaner($tempLoaner['id']);
    //         }
    //         return $tempLoaner['id'];
    //     }

    //     return $this->loaner->createLoaner($name, $email, $officeId);
    // }
}