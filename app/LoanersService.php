<?php
namespace App;

use App\App;
use App\Libs\{LoanersRepo, StatusRepo};

class LoanersService {
    protected StatusRepo $status;
    protected LoanersRepo $loaners;
    protected Database   $db;

    public function __construct() {
        try {
            $this->db       = App::getService('database');
            $this->status   = new StatusRepo($this->db);
            $this->loaners  = new LoanersRepo($this->db);
        } catch (\Throwable $t) {
            throw $t;
        }
    }

    public function create(string $name, string $email): array {
        return $this->loaners->create($name, $email);
    }

    public function findById(int $id): ?array {
        return $this->loaners->findById($id);
    }

    public function findByName(string $query): array {
        return $this->loaners->findByName($query);
    }

    public function findByEmail(string $email): ?array {
        return $this->loaners->findByEmail($email);
    }

    public function update(int $id, array $fields): bool {
        return $this->loaners->update($id, $fields);
    }

    public function deactivate(int $id): bool {
        return $this->loaners->deactivate($id);
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