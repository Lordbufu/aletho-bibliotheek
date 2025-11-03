<?php
/*  All default loaners table data:
 *      - [id]          = default index
 *      - [name]        = loaner name (not user name)
 *      - [email]       = loaners private email
 *      - [office_id]   = loaners office index
 *      - [active]      = loaner still active yes/no
 */

namespace App\Libs;

use App\{App, Database};

class LoanersRepo {
    protected Database $db;

    public function __construct($con = []) {
        if  (!empty($con)) {
            $this->db = $con;
        }
    }

    private function formatLoaner(array $row): array {
        return [
            'id'     => (int)$row['id'],
            'name'   => $row['name'],
            'email'  => $row['email'],
            'active' => (bool)$row['active'],
        ];
    }

    public function create(string $name, string $email): array {
        $this->db->query()->run(
            "INSERT INTO loaners (name, email, active) VALUES (?, ?, 1)",
            [$name, $email]
        );

        $id = (int)$this->db->query->lastInsertId();
        $row = $this->db->query()->fetchOne(
            "SELECT * FROM loaners WHERE id = ?",
            [$id]
        );

        return $this->formatLoaner($row);
    }

    public function findById(int $id): ?array {
        $row = $this->db->query()->fetchOne(
            "SELECT * FROM loaners WHERE id = ? AND active = 1",
            [$id]
        );
        
        return $row ? $this->formatLoaner($row) : null;
    }

    public function findByEmail(string $email): ?array {
        $row = $this->db->query()->fetchOne(
            "SELECT * FROM loaners WHERE email = ? AND active = 1",
            [$email]
        );

        return $row ? $this->formatLoaner($row) : null;
    }

    public function update(int $id, array $fields): bool {
        $set = [];
        $params = [];

        foreach ($fields as $key => $value) {
            $set[] = "$key = ?";
            $params[] = $value;
        }
        $params[] = $id;

        $result = $this->db->query()->run(
            "UPDATE loaners SET " . implode(", ", $set) . " WHERE id = ?",
            $params
        );

        return $result && $result->rowCount() > 0;
    }

    public function deactivate(int $id): bool {
        $result = $this->db->query()->run(
            "UPDATE loaners SET active = 0 WHERE id = ?",
            [$id]
        );

        return $result && $result->rowCount() > 0;
    }

    public function allActive(): array {
        $rows = $this->db->query()->fetchAll(
            "SELECT * FROM loaners WHERE active = 1"
        );

        return array_map(fn($row) => $this->formatLoaner($row), $rows);
    }

    public function getCurrentLoanerByBookId(int $bookId): ?array {
        $row = $this->db->query()->fetchOne(
            "SELECT l.*
            FROM book_status bs
            JOIN loaners l ON l.id = bs.loaner_id
            WHERE bs.book_id = ? AND (bs.active = 1 OR bs.active IS NULL)
            ORDER BY bs.start_date DESC
            LIMIT 1",
            [$bookId]
        );

        return $row ? $this->formatLoaner($row) : null;
    }

    public function getPreviousLoanersByBookId(int $bookId): array {
        $rows = $this->db->query()->fetchAll(
            "SELECT DISTINCT l.*
            FROM book_status bs
            JOIN loaners l ON l.id = bs.loaner_id
            WHERE bs.book_id = ?
            AND bs.active = 0
            AND l.name IS NOT NULL
            ORDER BY bs.start_date DESC",
            [$bookId]
        );

        return array_map(fn($row) => $this->formatLoaner($row), $rows);
    }
}