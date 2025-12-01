<?php
namespace App\Service;

use App\App;

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

    public function findById(int $id): ?array {
        return $this->loaners->findById($id);
    }

    public function findByName(string $partial): array {
        return $this->loaners->findByName($partial);
    }

    public function findOrCreateByEmail(string $name, string $email, int $office): ?array {
        return $this->loaners->findOrCreateByEmail($name, $email, $office);
    }

    public function getLoanersForDisplay(): ?array {
        return $this->loaners->getLoanersForDisplay();
    }

    public function getLoanersForLogic(?int $loanerId = null): array {
        return $this->loaners->getLoanersForLogic($loanerId);
    }

    public function createBookLoaner(int $bookId, int $loanerId, int $statusId, string $startDate, ?string $endDate): bool {
        return $this->loaners->createBookLoaner($bookId, $loanerId, $statusId, $startDate, $endDate);
    }

    public function deactivateLoaner(int $id): bool {
        return $this->loaners->deactivateLoaner($id);
    }

    public function deactivateActiveBookLoaners(int $bookId): bool {
        return $this->loaners->deactivateActiveBookLoaners($bookId);
    }

    public function update(int $id, array $fields): bool {
        return $this->loaners->update($id, $fields);
    }

    public function allActive(): array {
        return $this->loaners->allActive();
    }

    public function getCurrentLoanerNames(int $bookId): array {
        return $this->loaners->getLoanersByBookId($bookId, 'current', 'Geen huidige lener', 1, true);
    }

    public function getPreviousLoanerNames(int $bookId): array {
        return $this->loaners->getLoanersByBookId($bookId, 'previous', 'Geen vorige leners', 5, true);
    }

    public function getFullLoanerHistory(int $bookId): array {
        return $this->loaners->getLoanersByBookId($bookId, 'all', '', null, false);
    }
}