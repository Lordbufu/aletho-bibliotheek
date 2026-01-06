<?php
namespace App\Libs;

class BookRepo {
    protected \App\Database $db;

    public function __construct(\App\Database $db) {
        $this->db = $db;
    }

    /** Helper: Update a specific field, based on the parameters */
    protected function updateField(int $bookId, string $field, $value): bool {
        $query  = "UPDATE books SET {$field} = ? WHERE id = ?";
        $params = [$value, $bookId];

        return $this->db->query()->run($query, $params) !== false;
    }

    /** API: Find exact book(s) based on variable input conditions */
    public function findBooks(array $filters = [], bool $single = false): array {
        $sql    = "SELECT * FROM books";
        $where  = [];
        $params = [];

        $allowed = [
            'id'          => 'id = ?',
            'title'       => 'title = ?',
            'home_office' => 'home_office = ?',
            'cur_office'  => 'cur_office = ?',
            'active'      => 'active = ?'
        ];

        foreach ($allowed as $key => $condition) {
            if (array_key_exists($key, $filters)) {
                $where[]  = $condition;
                $params[] = $filters[$key];
            }
        }

        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        return $single
            ? $this->db->query()->fetchOne($sql, $params)
            : $this->db->query()->fetchAll($sql, $params);
    }

    /** API: Search fuzzy book(s) by input filter */
    public function searchBooks(array $filters = []): array {
        $sql    = "SELECT * FROM books";
        $where  = [];
        $params = [];

        $allowed = [
            'title'       => 'title LIKE ?',
            'home_office' => 'home_office LIKE ?',
            'cur_office'  => 'cur_office LIKE ?',
        ];

        foreach ($allowed as $key => $condition) {
            if (array_key_exists($key, $filters)) {
                $where[]  = $condition;
                $params[] = '%' . $filters[$key] . '%';
            }
        }

        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        if (!empty($filters['one'])) {
            $row = $this->db->query()->fetchOne($sql, $params);
            return $row ?? [];
        }
        
        return $this->db->query()->fetchAll($sql, $params);
    }

    /** API: Simple add book to table function returning the insterted ID */
    public function addBook(string $title, int $office): int {
        $query  = "INSERT INTO `books` (`title`, `home_office`, `cur_office`, `active`) VALUES (?, ?, ?, 1)";
        $params = [$title, $office, $office];

        $db = $this->db->query();
        $db->run($query, $params);
        
        return $db->lastInsertId();
    }

    /** API: Swap book object active state by ID */
    public function swapBookActiveState(int $bookId): bool {
        $query  = "UPDATE books SET active = CASE WHEN active = 1 THEN 0 ELSE 1 END WHERE id = ?";
        $stmt = $this->db->query()->run($query, [$bookId]);
        return ($stmt->rowCount() > 0);
    }

    /** API: Update the book title for edit functions */
    public function updateBookTitle(int $bookId, string $title): bool {
        return $this->updateField($bookId, 'title', $title);
    }

    /** API: Update the book office for edit functions */
    public function updateBookOffice(int $bookId, int $officeId, string $field): bool {
        $allowed = ['home_office', 'cur_office'];

        if (!in_array($field, $allowed, true)) {
            return false;
        }

        return $this->updateField($bookId, $field, $officeId);
    }

    /** Helper & API: Resolve a book its transport state */
    public function resolveTransport(array $book, ?int $loanerOffice, ?string $statusType): bool {
        if (!$loanerOffice) {
            return false;
        }
        
        $targetOffice = ($statusType === 'Afwezig')
            ? ($loanerOffice ?: (int)$book['home_office'])
            : (int)$book['cur_office'];

        return $book['cur_office'] !== $targetOffice;
    }
}