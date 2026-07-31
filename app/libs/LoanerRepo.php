<?php
namespace App\Libs;

use App\Libs\Context\LoanerContext;

final class LoanerRepo {
    private \App\Database $db;
    
    public function __construct() {
        $this->db = \App\App::getService('database');
    }

    /** Re-factored code */
    // Re-factor status: tested and working
    /** API: Fetch active loaner based on a book_id (not used atm ?) */
    public function getActiveLoanerByBookId(int $book_id): ?LoanerContext {
        $row = $this->db->query()->fetchOne("
            SELECT *
            FROM book_loaners bl
            JOIN loaners l
                ON bl.loaner_id = l.loaner_id
            WHERE bl.book_id = :book_id
            AND bl.is_active = 1
            ", [ "book_id" => $book_id ]
        );

        if (!$row) {
            return null;
        }

        return new LoanerContext($row);
    }

    // Re-factor status: W.I.P.
    /** API: Get loaner based on id */
    public function getLoanerById(int $loanerId): ?LoanerContext {
        $row = $this->db->query()->fetchOne("SELECT * FROM loaners WHERE loaner_id = :lId",
            [ "lId" => $loanerId ]
        );
        
        return $row ? new LoanerContext($row) : null;
    }

    /** API: Get loaner for  */

    // Re-factor status: tested and working
    /** API: Fetch all loaners based on multiple book_ids */
    public function getAllBookLoanersByBookId(mixed $book_ids): array {
        if (!$book_ids) {
            return [];
        }

        $book_ids = is_array($book_ids) ? $book_ids : [$book_ids];
        $book_ids = array_map('intval', $book_ids);
        $placeholders = [];
        $params = [];

        foreach ($book_ids as $i => $id) {
            $key = "book_id{$i}";
            $placeholders[] = ":{$key}";
            $params[$key] = $id;
        }

        $sql = "
            SELECT *
            FROM book_loaners bl
            JOIN loaners l ON bl.loaner_id = l.loaner_id
            WHERE bl.book_id IN (" . implode(',', $placeholders) . ")
            ORDER BY bl.book_id ASC, bl.start_at ASC
        ";

        $rows = $this->db->query()->fetchAll($sql, $params);
        $out = array_fill_keys($book_ids, []);

        foreach ($rows as $r) {
            $bid = (int)$r['book_id'];
            $out[$bid][] = new LoanerContext($r);
        }

        foreach ($out as $bid => $loaners) {
            if (empty($loaners)) {
                $out[$bid] = [];
                continue;
            }

            $active   = array_values(array_filter($loaners, fn($l) => $l->is_active));
            $inactive = array_values(array_filter($loaners, fn($l) => !$l->is_active));

            $ordered = [];

            if (!empty($active)) {
                $ordered[] = $active[0];
            }

            foreach ($inactive as $l) {
                $ordered[] = $l;
            }

            $out[$bid] = array_slice($ordered, 0, 5);
        }

        return $out;
    }

    // Re-factor status: tested and working
    /** API: Fetch loaners based on a variable (fuzzy) query input */
    public function findLoanerByName(string $query): ?array {
        $rows = $this->db->query()->fetchAll("
            SELECT *
            FROM loaners
            WHERE loaner_name LIKE :q
            ORDER BY loaner_name ASC
            LIMIT 20
        ", [
            ':q' => '%' . $query . '%'
        ]);

        return array_map(fn($r) => LoanerContext::fromRow($r), $rows);
    }

    // Re-factor status: W.I.P.
    /** API: Find loaner based on exact name */
    public function findLoanerByExactName(string $name): ?LoanerContext {
        $rows = $this->db->query()->fetchAll("SELECT * FROM loaners WHERE loaner_name = :lName",
            [ 'lName' => $name]
        );

        if (count($rows) === 0) {
            return null;
        }

        // Log a warning for the admin incase duplication does occure
        if (count($rows) > 1) {
            error_log("Warning: multiple loaners found with name '{$name}'. Using the first match.");
        }
        
        return new LoanerContext($rows[0]);
    }

    // Re-factor status: tested and working
    /** API: Get all active loaners */
    public function getAllActiveLoaners() {
        $rows = $this->db->query()->fetchAll("SELECT * FROM loaners WHERE is_active = 1");

        return array_map(fn($r) => LoanerContext::fromRow($r), $rows);
    }

    // Re-factor status: tested and working
    /** API: Create new loaner */
    public function createLoaner(string $name, string $email, int $officeId): int {
        $this->db->query()->run("
            INSERT INTO loaners (loaner_name, loaner_email, loaner_locId, is_active)
            VALUES (:lName, :lEmail, :lLocId, 1)
        ", [
            'lName'     => $name,
            'lEmail'    => $email,
            'lLocId'    => $officeId
        ]);

        return (int)$this->db->query()->lastInsertId();
    }

    // Re-factor status: W.I.P.
    /** API: Reactive loaner based on id */
        // public function reactivateLoaner(int $id): void {
        //     $this->db->query()->run("
        //         UPDATE loaners SET active = 1 WHERE id = :id
        //     ", ['id' => $id]);
        // }
}