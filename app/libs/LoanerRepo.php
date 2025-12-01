<?php
namespace App\Libs;

use App\App;

class LoanerRepo {
    protected ?array        $loaners = null;
    protected \App\Database $db;

    public function __construct(\App\Database $db) {
        $this->db = $db;
    }

    /** Helper: Format full `loaner` object (`loaners` + `book_loaners`), for service\controller\view logic */
    protected function formatLoaner(array $row): array {
        $loaner = [
            'id'        => (int)$row['id'],
            'name'      => $row['name'],
            'email'     => $row['email'],
            'office_id' => (int)$row['office_id']
        ];

        /** Deal with the potentially present `book_loaners` data */
        isset($row['status_id']) && $loaner['status_id'] = (int)$row['status_id'];
        isset($row['book_id'])  && $loaner['book_id'] = (int)$row['book_id'];
        isset($row['start_date']) && $loaner['start_date'] = $row['start_date'];
        isset($row['end_date'])   && $loaner['end_date']   = $row['end_date'];

        return $loaner;
    }

    /** Helper: Format only `loaners` names and `id`, for frontend purposes. */
    protected function formatLoanerForView(array $row): array {
        return ['id' => $row['id'], 'name' => $row['name']];
    }

    /** Helper: Create new `loaners` record */
    protected function insertLoaner(string $name, string $email, int $office): int {
        $query  = "INSERT INTO loaners (name, email, office_id, active) VALUES (?, ?, ?, 1)";
        $params =  [$name, $email, $office];
        $this->db->query()->run($query, $params);

        return (int)$this->db->query()->lastInsertId();
    }

    /** Helper: Create and return formatted full `loaners` object */
    protected function createLoaner(string $name, string $email, int $office): array {
        $id = $this->insertLoaner($name, $email, $office);
        return $this->findById($id);
    }

    /** Helper: Get/Set all active `loaners` to global */
    protected function setLoaners(): void {
        $query          = "SELECT * FROM loaners WHERE active = 1";
        $this->loaners  = $this->db->query()->fetchAll($query);
    }

    /** Helper: Find `loaners` by $email */
    protected function findByEmail(string $email): ?array {
        $query  = "SELECT * FROM loaners WHERE email = ? AND active = 1";
        $row    = $this->db->query()->fetchOne($query, [$email]);

        return $row ? $this->formatLoaner($row) : null;
    }

    /** API & Helper: get/find loaner by $id, returning full `loaners` object */
    public function findById(int $id): ?array {
        $query  = "SELECT * FROM loaners WHERE id = ? AND active = 1";
        $row    = $this->db->query()->fetchOne($query, [$id]);

        return $row ? $this->formatLoaner($row) : null;
    }

    /** API: Find loaners by partial name */
    public function findByName(string $partial): array {
        $query = "SELECT * FROM loaners WHERE name LIKE ? AND active = 1";
        $result = $this->db->query()->fetchAll($query, ["%$partial%"]);
        return $result ?: [];
    }

    /** API: get/find or create by $email */
    public function findOrCreateByEmail(string $name, string $email, int $office): ?array {
        return $this->findByEmail($email) ?? $this->createLoaner($name, $email, $office);
    }

    /** API: Create a book_loaners row and return boolean */
    public function createBookLoaner(int $bookId, int $loanerId, int $statusId, string $startDate, ?string $endDate): bool {
        $query = "INSERT INTO book_loaners (book_id, loaner_id, status_id, start_date, end_date, active)
                VALUES (?, ?, ?, ?, ?, 1)";
        $params = [$bookId, $loanerId, $statusId, $startDate, $endDate];
        $result = $this->db->query()->run($query, $params);
        return $result?->rowCount() > 0;
    }

    /** API: Get all currently active `loaners` for the frontend */
    public function getLoanersForDisplay(): ?array {
        if ($this->loaners === null) {
            $this->setLoaners();
        }

        $out = [];

        foreach ($this->loaners as $loaner) {
            $out[] = $this->formatLoanerForView($loaner);
        }

        return $out;
    }

    /** API: Get full active `loaners` objects for logic operations by $id */
    public function getLoanersForLogic(?int $loanerId = null): array {
        $query  = "SELECT l.*, bl.status_id, bl.book_id, bl.start_date, bl.end_date
                    FROM loaners l
                    LEFT JOIN book_loaners bl 
                    ON l.id = bl.loaner_id AND bl.active = 1
                    WHERE l.active = 1";
        $params = [];
        if ($loanerId !== null) {
            $query .= " AND l.id = ? ORDER BY bl.start_date DESC LIMIT 1";
            $params[] = $loanerId;
        }

        $rows = $loanerId !== null
            ? $this->db->query()->fetchAll($query, $params) // returns array of 0 or 1
            : $this->db->query()->fetchAll($query, $params);

        return array_map(fn($row) => $this->formatLoaner($row), $rows);
    }

    /** API: Deactivate active 'loaners' record */
    public function deactivateLoaner(int $id): bool {
        $query = "UPDATE loaners SET active = 0 WHERE id = ?";
        $result = $this->db->query()->run($query, [$id]);
        return $result?->rowCount() > 0;
    }

    /** Deactivate active book_loaners rows for a book */
    public function deactivateActiveBookLoaners(int $bookId): bool {
        $query = "UPDATE book_loaners SET active = 0 WHERE book_id = ? AND active = 1";
        $result = $this->db->query()->run($query, [$bookId]);
        return $result?->rowCount() >= 0;
    }

    /** API: Update `loaners` record */
    public function update(int $id, array $fields): bool {
        if (empty($fields)) {
            return false;
        }

        $set = [];
        $params = [];

        foreach ($fields as $key => $value) {
            $set[] = "$key = ?";
            $params[] = $value;
        }
        $params[] = $id;

        $query = "UPDATE loaners SET " . implode(", ", $set) . " WHERE id = ?";
        $result = $this->db->query()->run($query, $params);

        return $result?->rowCount() > 0;
    }

    /** API: Get loaners by book, with optional filters:
     *      @param int    $bookId   Book ID
     *      @param string $type     'current' | 'previous' | 'all'
     *      @param string $fallback Fallback string if no results (only applied for 'current'/'previous')
     *      @param int    $limit    Limit results (default null = no limit)
     *      @param bool   $namesOnly Return only names (true) or full formatted loaner objects (false)
     */
    public function getLoanersByBookId(int $bookId, string $type = 'all', string $fallback = '', ?int $limit = null, bool $namesOnly = false ): array {
        $where = "bl.book_id = ?";
        $params = [$bookId];

        if ($type === 'current') {
            $where .= " AND bl.active = 1";
        } elseif ($type === 'previous') {
            $where .= " AND bl.active = 0";
        }

        $query  = "SELECT l.*, bl.book_id, bl.status_id, bl.start_date, bl.end_date, bl.active AS bl_active
                    FROM book_loaners bl
                    JOIN loaners l ON l.id = bl.loaner_id
                    WHERE $where
                    ORDER BY bl.start_date DESC
                    " . ($limit ? "LIMIT $limit" : "");
        $rows   = $this->db->query()->fetchAll($query, $params);

        if (empty($rows) && $fallback !== '') {
            return [$fallback];
        }

        return $namesOnly
            ? array_map(fn($row) => $row['name'], $rows)
            : array_map(fn($row) => $this->formatLoaner($row), $rows);
    }
}