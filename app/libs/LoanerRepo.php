<?php
namespace App\Libs;

use App\Libs\Context\LoanerContext;

final class LoanerRepo {
    private \App\Database $db;
    
    public function __construct() {
        $this->db = \App\App::getService('database');
    }

    /** Helper: Map row to context */
    private function mapRowToLoaner($row): LoanerContext {
        $ctx = new LoanerContext();
        $ctx->id        = (int)$row['id'];
        $ctx->name      = (string)$row['name'];
        $ctx->email     = (string)$row['email'];
        $ctx->officeId  = (int)$row['office_id'];
        $ctx->active    = (bool)$row['active'];
        return $ctx;
    }

    /** API: Create new loaner */
    public function createLoaner(string $name, string $email, int $officeId): int {
        $this->db->query()->run("
            INSERT INTO loaners (name, email, office_id, active)
            VALUES (:name, :email, :officeId, 1)
        ", [
            'name' => $name,
            'email' => $email,
            'officeId' => $officeId
        ]);

        return (int)$this->db->query()->lastInsertId();
    }

    /** API: Reactive loaner based on id */
    public function reactivateLoaner(int $id): void {
        $this->db->query()->run("
            UPDATE loaners SET active = 1 WHERE id = :id
        ", ['id' => $id]);
    }

    /** API: Get loaner based on id */
    public function getLoanerById($loanerId): ?LoanerContext {
        $row = $this->db->query()->fetchOne("
            SELECT *
            FROM loaners
            WHERE id = :id
        ", [ 'id'    => $loanerId ]);

        return $row ? $this->mapRowToLoaner($row) : null;
    }

    /** API: Fetch loaners based on a variable (fuzzy) query input */
    public function findLoanerByName(string $query): array {
        $rows = $this->db->query()->fetchAll("
            SELECT *
            FROM loaners
            WHERE name LIKE :q
            ORDER BY name ASC
            LIMIT 20
        ", [
            ':q' => '%' . $query . '%'
        ]);

        $rows = $rows ?: [];

        return array_map([$this, 'mapRowToLoaner'], $rows);
    }

    /** API: Find loaner based on exact name */
    public function findLoanerByExactName(string $name): ?array {
        $rows = $this->db->query()->fetchAll("
            SELECT id, active
            FROM loaners
            WHERE name = :name
        ", [ 'name' => $name]
        );

        if (count($rows) === 0) {
            return null;
        }

        // Log a warning for the admin incase duplication does occure
        if (count($rows) > 1) {
            error_log("Warning: multiple loaners found with name '{$name}'. Using the first match.");
        }
        
        return $rows[0];
    }
}