<?php

namespace App\Libs;

use App\App;

/**
 * Very straight forward class, simply passing along database data, via global scope variables.
 */
class BookRepo {
    protected array $books;
    protected array $book;

    /**
     * Simple get all book table data to caller.
     * @return array
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

    /**
     * Simple get single book table data to caller.
     * @return array
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
}