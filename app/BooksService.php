<?php

namespace App;

use App\App;
use App\Libs\{BookRepo, GenreRepo, WriterRepo, StatusRepo, OfficeRepo};

class BooksService {
    protected BookRepo   $books;
    protected GenreRepo  $genres;
    protected WriterRepo $writers;
    protected StatusRepo $status;
    protected OfficeRepo $offices;
    protected Database   $db;

    /*  Construct all associated library classes, and logs it. */
    public function __construct() {
        $this->db      = App::getService('database');
        $this->books   = new BookRepo($this->db);
        $this->genres  = new GenreRepo($this->db);
        $this->writers = new WriterRepo($this->db);
        $this->status  = new StatusRepo($this->db);
        $this->offices = new OfficeRepo($this->db);
    }

    /*  Get all books as an array, processed and formatted for views. */
    public function getAllForDisplay(): array {
        $books = $this->books->findAll();
        $out  = [];

        foreach ($books as $book) {
            if (!$book['active']) {
                continue;
            }

            $out[] = [
                'id'     => $book['id'],
                'title'  => $book['title'],
                'writers' => $this->writers->getWriterNamesByBookId((int)$book['id']),
                'genres' => $this->genres->getGenreNamesByBookId((int)$book['id']),
                'office' => $this->offices->getOfficeNameByOfficeId((int)$book['office_id']),
                'status' => $this->status->getBookStatus((int)$book['id']),
                'dueDate' => $this->status->getBookDueDate((int)$book['id']),
                'curLoaner' => $this->status->getBookLoaner((int)$book['id']),
                'prevLoaners' => $this->status->getBookPrevLoaners((int)$book['id']),
                'canEditOffice' => App::getService('auth')->canManageOffice($book['office_id'])
            ];
        }

        // dd($out);

        return $out;
    }

    /*  Get all writer names, for frontend autocomplete JQuery. */
    public function getWritersForDisplay(): array {
        $temp = $this->writers->getAllWriters();
        $out = [];
        
        foreach ($temp as $writer) {
            if (!$writer['active']) {
                continue;
            }

            $out[] = $writer['name'];
        }

        return $out;
    }

    /*  Get all genre names, for frontend autocomplete JQuery. */
    public function getGenresForDisplay(): array {
        $temp = $this->genres->getAllGenres();
        $out = [];

        foreach ($temp as $genre) {
            if (!$genre['active']) {
                continue;
            }

            $out[] = $genre['name'];
        }

        return $out;
    }

    /*  Get all office names, for frontend autocomplete JQuery. */
    public function getOfficesForDisplay(): array {
        $temp = $this->offices->getAllOffices();
        $out = [];

        foreach ($temp as $office) {
            if (!$office['active']) {
                continue;
            }

            $out[] = $office['name'];
        }

        return $out;
    }

    /*  Add a new book, using our library classes. */
    public function addBook(array $data): mixed {
        $updated = false;

        // Temporary handling until many-to-many offices are supported
        $officeName = is_array($data['book_offices']) && count($data['book_offices']) > 0
            ? $data['book_offices'][0]
            : null;
        
        // Check if we still have the book data stored, if so set it to active agian.
        foreach($this->books->findAll() as $book) {
            if ($book['title'] === $data['book_name']) {
                if ($book['active']) {
                    return "This book name is already in the database";
                }

                $this->db->query()->run(
                    "UPDATE books SET active = 1 WHERE id = ?",
                    [$book['id']]
                );

                return true;
            }
        }

        $officeId = $this->offices->getOfficeIdByName($officeName);

        // Attempt to update book data within a PDO transaction.
        try {
            if (!$this->db->startTransaction()) {
                throw new \RuntimeException('Failed to start database transaction.');
            }

            // Attempt to store new book item and fetch its id
            if (!empty($data['book_name']) || !empty($data['book_offices'])) {
                $bookId = $this->books->addBook($data['book_name'], $officeId);
            }

            // Attempt to store genres
            if (!empty($data['book_genres'])) {
                $this->genres->addBookGenres($data['book_genres'], $bookId);
            }

            // Attempt to store writers
            if (!empty($data['book_writers'])) {
                $this->writers->addBookWriters($data['book_writers'], $bookId);
            }

            // Set default book status to 'Aanwezig'.
            $this->status->setBookStatus($bookId , 1);

            $this->db->finishTransaction();

            return true;
        } catch(\Throwable $e) {
            $this->db->cancelTransaction();
            return false;
        }
    }

    /*  Update book data, using our library classes. */
    public function updateBook(array $data): bool {
        if (empty($data['book_id']) || !is_numeric($data['book_id'])) {
            return false;
        }

        $officeId = is_array($data['book_offices']) && count($data['book_offices']) > 0
            ? $data['book_offices'][0]
            : null;

        // Attempt to update book data within a PDO transaction.
        try {
            if (!$this->db->startTransaction()) {
                throw new \RuntimeException('Failed to start database transaction.');
            }

            if (!empty($data['book_name'])) {
                $this->books->updateBookTitle($data['book_id'], $data['book_name']);
            }

            if (!empty($data['book_offices'])) {
                $this->books->updateBookOffice($data['book_id'], $officeId);
            }

            if (!empty($data['book_genres'])) {
                $this->genres->updateBookGenres($data['book_id'], $data['book_genres']);
            }

            if (!empty($data['book_writers'])) {
                $this->writers->updateBookWriters($data['book_id'], $data['book_writers']);
            }

            $this->db->finishTransaction();

            return true;
        } catch(\Throwable $e) {
            $this->db->cancelTransaction();

            App::getService('logger')->error(
                "The 'BooksService' failed to update book data: {$e->getMessage()}",
                'bookservice'
            );

            return false;
        }
    }

    /**
     * 
     */
    public function deleteBook(int $bookId): bool {
        // simply set the 'active' field to 0
    }
}