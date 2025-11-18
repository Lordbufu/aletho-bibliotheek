<?php
namespace App\Libs;

use App\App;

/* Your basic books library, dealing with all books table data & relations . */
class BookRepo {
    protected array         $books;
    protected array         $book;
    protected \App\Database $db;

    public function __construct() {
        $this->db = App::getService('database');
    }

    /** Simple get all book table data to caller.
     *      @return array   -> All books in the database.
     */
    public function findAll(): array {
        $this->books = $this->db->query()->fetchAll("SELECT * FROM books");
        return $this->books;
    }

    /** Simple get single book table data to caller.
     *      @param int $id      -> The book ID.
     *      @return array       -> The book its row data
     */
    public function findOne(int $id): array {
        $this->book = $this->db->query()->fetchOne("SELECT * FROM books WHERE id = ?", [$id]);
        return $this->book;
    }

    /** Simple add book to table function.
     *      @param string $title    -> Sanitized user input.
     *      @param int $office      -> Index extracted from `OfficeRepo`.
     *      @return int             -> Index of the last inserted db row.
     */
    public function addBook(string $title, int $office) {
        $this->db->query()->run(
                "INSERT INTO `books` (`title`, `office_id`, `active`) VALUES (?, ?, 1)",
                [$title, $office]
        );
        
        return $this->db->query()->lastInsertId();
    }

    /*  Disabled book by id. */
    public function disableBook(int $bookId): bool {
        $result = $this->db->query()->run(
            "UPDATE books SET active = 0 WHERE id = ?",
            [$bookId]
        );
            
        return $result !== false;
    }

    /** Update the book title for edit functions.
     *      @param int $bookId      -> The book ID.
     *      @param string $title    -> The new book title.
     *      @return bool            -> Success status.
     */
    public function updateBookTitle(int $bookId, string $title): bool {
        $result = $this->db->query()->run(
            "UPDATE books SET title = ? WHERE id = ?",
            [$title, $bookId]
        );
            
        return $result !== false;
    }

    /** Update the book office for edit functions.
     *      @param int $bookId      -> The book ID.
     *      @param int $officeId    -> The new office ID.
     *      @return bool            -> Success status.
     */
    public function updateBookOffice(int $bookId, int $officeId): bool {
        $result = $this->db->query()->run(
            "UPDATE books SET office_id = ? WHERE id = ?",
            [$officeId, $bookId]
        );

        return $result !== false;
    }
}