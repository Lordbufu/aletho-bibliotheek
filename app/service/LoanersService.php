<?php
namespace App\Service;

use App\App;

/** Loaners mental notes, for cleanup purposes:
 *  Potentially useless functions in this service:
 *      Safe to remove after testing:
 *          getLoanersForLogic() (already replaced)
 *          deactivateLoaner() (if no controller uses it)
 *          getLoanersForDisplay() (if no controller uses it)
 *          update() (if no future need)
 *          Service wrappers for unused repo methods
 *
 *      Keep in repo even if unused:
 *          findById()
 *          mergeLoanerAssignment()
 *          getLoanerWithActiveAssignment()
 *          getAllLoanersWithActiveAssignments()
 *          getFullLoanerHistory()
 */

class LoanersService {
    protected \App\Libs\LoanerRepo  $loaners;
    protected \App\Database         $db;

    public function __construct() {
        try {
            $this->loaners  = App::getLibrary('loaner');
            $this->db       = App::getService('database');
        } catch (\Throwable $t) {
            throw $t;
        }
    }

    /** Helper: Evaluate assignment state for status changes */
    protected function shouldAssignLoaner(int $statusId): bool {
        return !in_array($statusId, [1, 3], true);
    }

    /** Helper: Evaluate if loaners should be deactivated */
    protected function shouldDeactivateLoaners(?string $statusType): bool {
        return $statusType === 'Aanwezig';
    }

    /** Helper: Return fallback if no rows found */
    protected function fallbackIfEmpty(array $rows, string $fallback): array {
        return empty($rows) ? [$fallback] : $rows;
    }

    // === Thin wrappers, linking the library(repo) to our service ===
    public function findLoanerByName(string $partial): array {
        return $this->loaners->findLoanerByName($partial);
    }

    public function findOrCreateLoanerByEmail(string $name, string $email, int $office): ?array {
        return $this->loaners->findOrCreateLoanerByEmail($name, $email, $office);
    }

    public function deactivateActiveBookLoaners(int $bookId): bool {
        return $this->loaners->deactivateActiveBookLoaners($bookId);
    }

    // === More logic driven wrappers, using the Library as source of truth ===
    /** API: Get current loaner full object for a book */
    public function getCurrentLoaner(int $bookId): array {
        $rows = $this->fallbackIfEmpty(
            $this->loaners->getActiveLoanersByBookId($bookId),
            'Geen huidige lener'
        );

        return [ $rows[0] ];
    }

    /** API: Get current loaner names for a book */
    public function getCurrentLoanerNames(int $bookId): array {
        $rows = $this->fallbackIfEmpty(
            $this->loaners->getActiveLoanersByBookId($bookId),
            'Geen huidige lener'
        );

        /** Ensure the fallback is returned correctly */
        if (isset($rows[0]) && is_string($rows[0])) {
            return $rows;
        }

        return array_column($rows, 'name');
    }

    /** API: Get previous 5 loaner names for a book */
    public function getPreviousLoanerNames(int $bookId): array {
        $rows = $this->fallbackIfEmpty(
            $this->loaners->getInactiveLoanersByBookId($bookId),
            'Geen vorige leners'
        );

        /** Ensure the fallback is returned correctly */
        if (isset($rows[0]) && is_string($rows[0])) {
            return $rows;
        }

        return array_slice(array_column($rows, 'name'), 0, 5);
    }

    /** API: Assign loaner if required */
    public function assignBookLoanerIfNeeded(int $bookId, ?array $loaner, int $statusId, array $requestStatus): bool {
        if (!$loaner || !$this->shouldAssignLoaner($statusId)) {
            return true;
        }

        $periodeLength = (int)($requestStatus['periode_length'] ?? 0);
        $dueDate = calculateDueDate(null, $periodeLength);

        return $this->loaners->assignLoanerToBook($bookId, $loaner['id'], $statusId, $dueDate);
    }

    /** API: Deactivate `book_loaner` is needed */
    public function deactivateBookLoanersIfNeeded(int $bookId, array $requestStatus): bool {
        $statusType = $requestStatus['type'] ?? null;

        if (!$this->shouldDeactivateLoaners($statusType)) {
            return true;
        }

        $result = $this->deactivateActiveBookLoaners($bookId);

        return $result !== false;
    }

    // Potentially useless function?
    public function updateLoaner(int $id, array $fields): bool {
        return $this->loaners->update($id, $fields);
    }

    // Potentially useless function?
    public function findLoanerById(int $id): ?array {
        return $this->loaners->findById($id);
    }

    // Potentially useless function?
    public function getLoanersForDisplay(): ?array {
        return $this->loaners->getLoanersForDisplay();
    }

    // Potentially useless function?
    public function deactivateLoaner(int $id): bool {
        return $this->loaners->deactivateLoaner($id);
    }

    // Potentially useless function?
    public function getFullLoanerHistory(int $bookId): array {
        return $this->loaners->getAllLoanersByBookId($bookId);
    }

    // Potentially useless function?
    /** API: Get loaners for logic operations */
    public function getLoanersForLogic(?int $loanerId = null): array {
        if ($loanerId !== null) {
            $loaner = $this->loaners->getLoanerWithActiveAssignment($loanerId);
            return $loaner ? [$loaner] : [];
        }

        return $this->loaners->getAllLoanersWithActiveAssignments();
    }
}