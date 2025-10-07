<?php

namespace App\Libs;

use App\App;

/* Your basic books library, dealing with all books table data & relations . */
class BookRepo {
    protected array $books;
    protected array $book;

    /** Simple get all book table data to caller.
     *      @return array
     */
    public function findAll(): array {
        $this->books = App::getService('database')
            ->query()
            ->fetchAll("SELECT * FROM books");
        
        if (!is_array($this->books) || $this->books === []) {
            App::getService('logger')->error(
                "The 'BookRepo' dint get any books from the database",
                'bookservice'
            );
        }

        return $this->books;
    }

    /** Simple get single book table data to caller.
     *      @param int $id The book ID.
     *      @return array
     */
    public function findOne(int $id): array {
        $this->book = App::getService('database')
            ->query()
            ->fetchOne("SELECT * FROM books WHERE id = ?", [$id]);
            
        if (!is_array($this->book) || $this->book === []) {
            App::getService('logger')->error(
                "The 'BookRepo' dint get any books from the database",
                'bookservice'
            );
        }

        return $this->book;
    }

    /** Update the book title for edit functions.
     *      @param int $bookId The book ID.
     *      @param string $title The new book title.
     *      @return bool Success status.
     */
    public function updateBookTitle(int $bookId, string $title): bool {
        $result = App::getService('database')
            ->query()
            ->run(
                "UPDATE books SET title = ? WHERE id = ?",
                [$title, $bookId]
            );
            
        return $result !== false;
    }

    /** Update the book office for edit functions.
     *      @param int $bookId The book ID.
     *      @param int $officeId The new office ID.
     *      @return bool Success status.
     */
    public function updateBookOffice(int $bookId, int $officeId): bool {
        $result = App::getService('database')
            ->query()
            ->run(
                "UPDATE books SET office_id = ? WHERE id = ?",
                [$officeId, $bookId]
            );

        return $result !== false;
    }
}