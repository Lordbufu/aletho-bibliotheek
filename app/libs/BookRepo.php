<?php
namespace App\Libs;

/** Your basic books library, dealing with all books table data & relations . */
class BookRepo {
    protected array         $books = [];
    protected array         $book  = [];
    protected \App\Database $db;

    public function __construct(\App\Database $db) {
        $this->db = $db;
    }

    /** API: Get all books data */
    public function findAllBooks(): array {
        $query = "SELECT * FROM books";
        $this->books = $this->db->query()->fetchAll($query);
        return $this->books;
    }

    /** API: Get one books object by ID */
    public function findOneBook(int $id): array {
        $query  = "SELECT * FROM books WHERE id = ?";
        $params = [$id];
        $this->book = $this->db->query()->fetchOne($query, $params);
        return $this->book;
    }

    /** Simple add book to table function. */
    public function addBook(string $title, int $office) {
        $query  = "INSERT INTO `books` (`title`, `home_office`, `cur_office`, `active`) VALUES (?, ?, ?, 1)";
        $params = [$title, $office, $office];

        $this->db->query()->run($query, $params);
        
        return $this->db->query()->lastInsertId();
    }

    /** Swap book object active state by ID */
    public function swapBookActiveState(int $statusId): bool {
        $query  = "UPDATE books SET active = CASE WHEN active = 1 THEN 0 ELSE 1 END WHERE id = ?";
        $stmt = $this->db->query()->run($query, [$statusId]);
        return ($stmt->rowCount() > 0);
    }

    /** Update the book title for edit functions */
    public function updateBookTitle(int $bookId, string $title): bool {
        $query  = "UPDATE books SET title = ? WHERE id = ?";
        $params = [$title, $bookId];

        $result = $this->db->query()->run($query, $params);
            
        return $result !== false;
    }

    /** Update the book office for edit functions. */
    public function updateBookOffice(int $bookId, int $officeId): bool {
        $query  = "UPDATE books SET home_office = ? WHERE id = ?";
        $params = [$officeId, $bookId];

        $result = $this->db->query()->run($query, $params);

        return $result !== false;
    }

    /** Helper & API: Figure out where a book should goto next */
    public function resolveReturnTarget(array $book, int $loanerOffice, string $statusType): int {
        if ($statusType === 'Afwezig') {
            if (!empty($loanerOffice)) {
                return $loanerOffice;
            }

            return (int)$book['home_office'];
        }

        return (int)$book['cur_office'];
    }

    /** Helper & API: Resolve a book its transport state */
    public function resolveTransport(array $book, ?int $loanerOffice, ?string $statusType): bool {
        if (empty($loanerOffice)) {
            return false;
        }

        $targetOffice = $this->resolveReturnTarget($book, $loanerOffice, $statusType);

        return $book['cur_office'] !== $targetOffice;
    }
}