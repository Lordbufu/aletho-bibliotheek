<?php
namespace App\Services;

use App\App;
use App\Libs\{LoanerRepo, LocationRepo};
use App\Libs\Context\LoanerContext;
use App\ViewModel\LoanerViewModel;

final class LoanerService {
    private LoanerRepo   $loaner;
    private LocationRepo $location;

    public function __construct() {
        $this->loaner   = new LoanerRepo();
        $this->location  = new LocationRepo();
    }

    /** Re-factored/new functions */
    /** Facade: Get all active loaners from the database*/
    public function getAllActiveLoaners() {
        return $this->loaner->getAllActiveLoaners();
    }

    /** Facade: Get active loaner for a book_id */
    public function getActiveLoanerByBookId(int $book_id): ?LoanerContext {
        return $this->loaner->getActiveLoanerByBookId($book_id);
    }

    /** Facade: Get loaner by loaner_id */
    public function getLoanerById(int $loaner_id): ?LoanerContext {
        return $this->loaner->getLoanerById($loaner_id);
    }

    /** Facade: Get all book loaners for a book_id */
    public function getAllBookLoanersByBookId(mixed $book_ids): array {
        return $this->loaner->getAllBookLoanersByBookId($book_ids);
    }

    /** Facade: Find loaner by name for frontend (fuzzy search) */
    public function findLoanerByName(string $query): ?array {
        return $this->loaner->findLoanerByName($query);
    }

    /** Facade: Find loaner bu exact name */
    public function findLoanerByExactName(string $name): ?LoanerContext {
        return $this->loaner->findLoanerByExactName($name);
    }

    /** Facade: Create a new loaner record */
    public function createLoaner(string $loaner_name, string $loaner_email, int $loanerLocId): int {
        return $this->loaner->createLoaner($loaner_name, $loaner_email, $loanerLocId);
    }

    // Re-factor status: tested and working
    /** API: Provide singleton data context for frontend XHR requests */
    public function getLoanerForBook(int $bookId): ?LoanerViewModel {
        $bookLoaner = $this->loaner->getActiveLoanerByBookId($bookId);
        if ($bookLoaner === null) {
            return null;
        }

        $location   = $this->location->getLocationContextById($bookLoaner->loaner_locId);
        return new LoanerViewModel($bookLoaner, $location);
    }

    // Re-factor status: tested and working
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

    // Re-factor status: W.I.P.
    /** API: Get or create a loaner for status change flows */
    public function getOrCreateLoaner(array $data): LoanerContext {
        $loanerCtx      = $this->findLoanerByExactName($data['loaner_name']);
        
        if (!$loanerCtx) {
            $tId        = $this->createLoaner($data['loaner_name'], $data['loaner_email'], $data['loaner_location']);
            $loanerCtx  = $this->getLoanerById($tId);
        }
        
        return $loanerCtx;
    }
}

    // Re-factor status: Evertything below still needs a review.
    // /** Facade: Find loaner by exact name for API logic */
    // public function findLoanerByExactName(string $name): ?LoanerContext {
    //     return $this->loaner->findLoanerByExactName($name);
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