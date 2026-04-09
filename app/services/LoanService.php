<?php
namespace App\Services;

use App\Libs\LoanRepo;
use App\Libs\Context\LoanContext;

final class LoanService {
    private LoanRepo    $loan;

    public function __construct() {
        $this->loan     = new LoanRepo();
    }

    /** Facade: Get current active loan by status and book id */
    public function getCurrentLoanById(int $statusId, int $bookId): ?LoanContext {
        return $this->loan->getCurrentLoanById($statusId, $bookId);
    }

    /** Facade: Get active loans for book */
    public function getActiveLoansForBook(int $bookId): ?LoanContext {
        return $this->loan->getActiveLoansForBook($bookId);
    }

    /** Facade: Get all previous loans by book id */
    public function getPreviousLoansByBookId(int $bookId): ?array {
        return $this->loan->getPreviousLoansByBookId($bookId);
    }

    /** Facade: Create new loan for status transitions */
    public function createLoan(int $bookId, int $loanerId, int $statusId, ?\DateTimeImmutable $startDate, ?\DateTimeImmutable $endDate, bool $active): int {
        return $this->loan->createLoan($bookId, $loanerId, $statusId, $startDate, $endDate, $active);
    }

    /** Facade: Update loan for status transitions */
    public function updateLoan(int $loanId, int $statusId, ?\DateTimeImmutable $startDate, ?\DateTimeImmutable $endDate, bool $active): void {
        $this->loan->updateLoan($loanId, $statusId,  $startDate, $endDate, $active);
    }

    /** Facade: Deactive loan row for a specific loan */
    public function deactivateLoan(int $loanId): void {
        $this->loan->deactivateLoan($loanId);
    }

    // /** Facade: Get latstest loaner row for specific book */
    // public function getLatestLoanerRowForBook(int $bookId): ?LoanContext {
    //     return $this->loan->getLatestLoanerRowForBook($bookId);
    // }
}