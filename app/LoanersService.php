<?php
namespace App;

use App\App;

class LoanersService {
    protected \App\Libs\LoanersRepo   $loaners;

    public function __construct() {
        try {
            $this->loaners = new \App\Libs\LoanersRepo();
        } catch (\Throwable $t) {
            throw $t;
        }
    }

    public function findById(int $id): ?array {
        return $this->loaners->findById($id);
    }

    public function findByEmail(string $email): ?array {
        return $this->loaners->findByEmail($email);
    }

    public function findOrCreateByEmail(string $name, string $email, int $office): ?array {
        return $this->loaner->findOrCreateByEmail($name, $email, $office);
    }

    public function deactivate(int $id): bool {
        return $this->loaners->deactivate($id);
    }

    public function update(int $id, array $fields): bool {
        return $this->loaners->update($id, $fields);
    }

    public function allActive(): array {
        return $this->loaners->allActive();
    }

    public function getCurrentLoanerByBookId(int $bookId): ?array {
        return $this->loaners->getCurrentLoanerByBookId($bookId);
    }

    public function getPreviousLoanersByBookId(int $bookId): array {
        return $this->loaners->getPreviousLoanersByBookId($bookId);
    }
}