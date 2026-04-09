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

    /** API: Get loaner by 'loaners'.'id' */
    public function getLoanerById($loanerId): ?LoanerContext {
        $row = $this->db->query()->fetchOne("
            SELECT *
            FROM loaners
            WHERE id = :id
        ", [ 'id'    => $loanerId ]);

        return $row ? $this->mapRowToLoaner($row) : null;
    }

    /** API: Find loaner by email, if non found create new loaner */
    public function findOrCreateLoaner(string $name, string $email, string $location): int {
        // 1. Try to find existing loaner by email
        $row = $this->db->query()->fetchOne("
            SELECT id
            FROM loaners
            WHERE email = :email
            LIMIT 1
        ", [
            'email' => $email
        ]);

        if ($row) {
            return (int)$row['id'];
        }

        // 2. Create new loaner
        $this->db->query()->run("
            INSERT INTO loaners (name, email, location, created_at)
            VALUES (:name, :email, :location, NOW())
        ", [
            'name' => $name,
            'email' => $email,
            'location' => $location
        ]);

        return (int)$this->db->query()->lastInsertId();
    }

    /** API: Fetch loaners based on a variable query input */
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
}