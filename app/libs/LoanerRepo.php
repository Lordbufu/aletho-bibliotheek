<?php
namespace App\Libs;

class LoanerRepo {
    protected ?array        $cachedActiveLoanersForDisplay = null;
    protected \App\Database $db;

    public function __construct(\App\Database $db) {
        $this->db = $db;
    }

    /** Helper: Format a loaner row into a standard array */
    protected function formatLoaner(array $row): array {
        return [
            'id'        => (int)$row['id'],
            'name'      => $row['name'],
            'email'     => $row['email'],
            'office_id' => (int)$row['office_id'],
        ];
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
        $this->cachedActiveLoanersForDisplay = null;

        return (int)$this->db->query()->lastInsertId();
    }

    /** Helper: Create and return formatted full `loaners` object */
    protected function createLoaner(string $name, string $email, int $office): array {
        $id = $this->insertLoaner($name, $email, $office);
        return $this->findById($id);
    }

    /** Helper: Get/Set all active `loaners` to global */
    protected function loadActiveLoanersForDisplay(): void {
        $query                                  = "SELECT * FROM loaners WHERE active = 1";
        $this->cachedActiveLoanersForDisplay    = $this->db->query()->fetchAll($query);
    }

    /** Helper: Find `loaners` by $email */
    protected function findByEmail(string $email): ?array {
        $query  = "SELECT * FROM loaners WHERE email = ? AND active = 1";
        $row    = $this->db->query()->fetchOne($query, [$email]);
        return $row ? $this->formatLoaner($row) : null;
    }

    // Potentially useless function?
    // Maybe remove the API link, and keep it as helper ?
    /** API & Helper: get/find loaner by $id, returning full `loaners` object */
    public function findById(int $id): ?array {
        $query  = "SELECT * FROM loaners WHERE id = ? AND active = 1";
        $row    = $this->db->query()->fetchOne($query, [$id]);
        return $row ? $this->formatLoaner($row) : null;
    }

    /** API: Find loaners by partial name */
    public function findLoanerByName(string $partial): array {
        $query = "SELECT * FROM loaners WHERE name LIKE ? AND active = 1";
        $rows = $this->db->query()->fetchAll($query, ["%$partial%"]);

        return array_map(fn($row) => $this->formatLoaner($row), $rows ?: []);
    }

    /** API: get or create by $email */
    public function findOrCreateLoanerByEmail(string $name, string $email, int $office): ?array {
        $loaner = $this->findByEmail($email);

        if ($loaner) {
            return $loaner;
        }

        $created = $this->createLoaner($name, $email, $office);
        return (!empty($created['id'])) ? $created : null;
    }

    /** API: Get all loaners by book ID, regardless of active status */
    public function getActiveLoanersByBookId(int $bookId): array {
        $query = "SELECT l.*, bl.book_id, bl.status_id, bl.start_date, bl.end_date, bl.active AS bl_active
                    FROM book_loaners bl
                    JOIN loaners l ON l.id = bl.loaner_id
                    WHERE bl.book_id = ? AND bl.active = 1
                    ORDER BY bl.start_date DESC";

        return $this->db->query()->fetchAll($query, [$bookId]);
    }

    /** API: Get inactive loaners by book ID */
    public function getInactiveLoanersByBookId(int $bookId): array {
        $query = "SELECT l.*, bl.book_id, bl.status_id, bl.start_date, bl.end_date, bl.active AS bl_active
                    FROM book_loaners bl
                    JOIN loaners l ON l.id = bl.loaner_id
                    WHERE bl.book_id = ? AND bl.active = 0
                    ORDER BY bl.start_date DESC";

        return $this->db->query()->fetchAll($query, [$bookId]);
    }

    /** API: Get full loaner history for a book */
    public function getAllLoanersByBookId(int $bookId): array {
        $query = "SELECT l.*, bl.book_id, bl.status_id, bl.start_date, bl.end_date, bl.active AS bl_active
                    FROM book_loaners bl
                    JOIN loaners l ON l.id = bl.loaner_id
                    WHERE bl.book_id = ?
                    ORDER BY bl.start_date DESC";

        return $this->db->query()->fetchAll($query, [$bookId]);
    }

    /** API: Deactivate active book_loaners rows for a book */
    public function deactivateActiveBookLoaners(int $bookId): bool {
        $query = "UPDATE book_loaners SET active = 0 WHERE book_id = ? AND active = 1";
        $result = $this->db->query()->run($query, [$bookId]);
        return $result?->rowCount() >= 0;
    }

    /** API: Create a book_loaners row and return rowCount (bool) */
    public function assignLoanerToBook(int $bookId, int $loanerId, int $statusId, ?string $endDate): bool {
        $query = "INSERT INTO book_loaners (book_id, loaner_id, status_id, end_date, active)
                VALUES (?, ?, ?, ?, 1)";
        $params = [$bookId, $loanerId, $statusId, $endDate];
        $result = $this->db->query()->run($query, $params);
        return $result?->rowCount() > 0;
    }

    // Potentially useless function?
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
        $this->cachedActiveLoanersForDisplay = null;

        return $result?->rowCount() > 0;
    }

    // Potentially useless function?
    /** API: Get all currently active `loaners` for the frontend */
    public function getLoanersForDisplay(): array {
        if ($this->cachedActiveLoanersForDisplay === null) {
            $this->loadActiveLoanersForDisplay();
        }

        return array_map(
            fn($l) => $this->formatLoanerForView($l),
            $this->cachedActiveLoanersForDisplay
        );
    }

    // Potentially useless function?
    /** API: Deactivate active 'loaners' record */
    public function deactivateLoaner(int $id): bool {
        $query = "UPDATE loaners SET active = 0 WHERE id = ?";
        $result = $this->db->query()->run($query, [$id]);
        $this->cachedActiveLoanersForDisplay = null;
        return $result?->rowCount() > 0;
    }

    // Potentially useless function?
    /** Helper: Merge book_loaners data into loaner array */
    protected function mergeLoanerAssignment(array $loaner, array $row): array {
        if (isset($row['status_id']))  $loaner['status_id']  = (int)$row['status_id'];
        if (isset($row['book_id']))    $loaner['book_id']    = (int)$row['book_id'];
        if (isset($row['start_date'])) $loaner['start_date'] = $row['start_date'];
        if (isset($row['end_date']))   $loaner['end_date']   = $row['end_date'];

        return $loaner;
    }

    // Potentially useless function?
    /** API: Get loaner with active assignment by loaner ID */
    public function getLoanerWithActiveAssignment(int $loanerId): ?array {
        $query = "SELECT l.*, bl.status_id, bl.book_id, bl.start_date, bl.end_date
                FROM loaners l
                LEFT JOIN book_loaners bl 
                    ON l.id = bl.loaner_id AND bl.active = 1
                WHERE l.active = 1 AND l.id = ?
                ORDER BY bl.start_date DESC
                LIMIT 1";

        $row = $this->db->query()->fetchOne($query, [$loanerId]);

        if (!$row) {
            return null;
        }

        $loaner = $this->formatLoaner($row);
        return $this->mergeLoanerAssignment($loaner, $row);
    }

    // Potentially useless function?
    /** API: Get all loaners with active assignments */
    public function getAllLoanersWithActiveAssignments(): array {
        $query = "SELECT l.*, bl.status_id, bl.book_id, bl.start_date, bl.end_date
                    FROM loaners l
                    LEFT JOIN book_loaners bl 
                        ON l.id = bl.loaner_id AND bl.active = 1
                    WHERE l.active = 1";

        $rows = $this->db->query()->fetchAll($query);

        return array_map(function($row) {
            $loaner = $this->formatLoaner($row);
            return $this->mergeLoanerAssignment($loaner, $row);
        }, $rows);
    }
}