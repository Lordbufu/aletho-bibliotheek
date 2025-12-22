<?php
namespace App\Libs;

/** BookRepo:
 *
 * Repository layer for all data operations related to the `books` table.
 * This class provides a clean, domain‑focused API for retrieving, searching,
 * creating, and updating book records. It acts as the single source of truth
 * for all book‑specific persistence logic.
 *
 * Responsibilities:
 * -----------------
 * - Fetching book records using exact filters (`findBooks`)
 * - Performing fuzzy/partial searches on selected fields (`searchBooks`)
 * - Creating new book entries (`addBook`)
 * - Updating individual book fields (title, home_office, cur_office)
 * - Toggling the active state of a book (`swapBookActiveState`)
 * - Determining return/transport logic for book movement workflows
 *
 * Data Ownership:
 * ---------------
 * This repository manages only the fields stored directly in the `books` table:
 *   - id
 *   - title
 *   - home_office
 *   - cur_office
 *   - active
 *
 * All other book‑related information (writers, genres, categories, metadata)
 * belongs to other domain repositories and is intentionally not handled here.
 *
 * Usage Notes:
 * ------------
 * - Controllers should interact with this class through the corresponding
 *   Service layer, which provides intention‑revealing wrapper methods.
 *
 * - `findBooks()` performs strict, exact‑match lookups and is intended for
 *   business‑logic‑driven queries (e.g., fetching a specific book or all active books).
 *
 * - `searchBooks()` performs partial (LIKE‑based) matching and is intended for
 *   user‑facing search features where input may be incomplete or fuzzy.
 *
 * - Update operations are field‑specific and validated internally to prevent
 *   unsafe column updates.
 *
 * - Helper methods (`resolveReturnTarget`, `resolveTransport`) support
 *   movement/loan workflows and encapsulate domain rules for determining
 *   where a book should be routed next.
 *
 * This repository is designed to remain stateless, predictable, and easy to
 * extend as the `books` domain evolves.
 */
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
            return $row ? [$row] : [];
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