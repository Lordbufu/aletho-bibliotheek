<?php

/*  Dealing with errrors and user feedback:
 *      $_SESSION['_flash'] = [
 *          'type'      => 'failure'|'success', 
 *          'message'   => '...'
 *      ]
 */

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
        $this->books   = new BookRepo();
        $this->genres  = new GenreRepo();
        $this->writers = new WriterRepo();
        $this->status  = new StatusRepo();
        $this->offices = new OfficeRepo();
        $this->db      = App::getService('database');

        App::getService('logger')->info(
            "Service 'books' has constructed its libraries, and loaded the database service",
            'bookservices'
        );
    }

    /** Get all books as an array, processed and formatted for views.
     *      @return array
     */
    public function getAllForDisplay(): array {
        $rows = $this->books->findAll();
        $out  = [];

        foreach ($rows as $row) {
            $out[] = [
                'id'     => (int)$row['id'],
                'title'  => $row['title'],
                'writers' => $this->writers->getWriterNamesByBookId((int)$row['id']),
                'genres' => $this->genres->getGenreNamesByBookId((int)$row['id']),
                'office' => $this->offices->getOfficeNameByOfficeId((int)$row['office_id']),
                'status' => $this->status->getDisplayStatusByBookId((int)$row['id']),
                'canEditOffice' => (
                    $_SESSION['user']['office'] === 'All'
                    || $_SESSION['user']['office'] === $row['office_id']
                    ) ? 1 : 0,
            ];
        }

        // dd($out);
        return $out;
    }

    /** Get all writer names, for frontend autocomplete JQuery.
     *      @return array
     */
    public function getWritersForDisplay(): array {
        $temp = $this->writers->getAllWriters();
        $out = [];
        
        foreach ($temp as $writer) {
            $out[] = $writer['name'];
        }

        return $out;
    }

    /** Get all genre names, for frontend autocomplete JQuery.
     *      @return array
     */
    public function getGenresForDisplay(): array {
        $temp = $this->genres->getAllGenres();
        $out = [];

        foreach ($temp as $genre) {
            $out[] = $genre['name'];
        }

        return $out;
    }

    /** Get all office names, for frontend autocomplete JQuery.
     *      @return array
     */
    public function getOfficesForDisplay(): array {
        $temp = $this->offices->getAllOffices();
        $out = [];

        foreach ($temp as $office) {
            $out[] = $office['name'];
        }

        return $out;
    }

    /** Add a new book, using our library classes.
     *      @param array $data Associative array with book data to add.
     *         Expected keys: title (string), writers (array), genres (array), offices (array)
     *      @return bool True on success, false on failure.
     */
    public function addBook(array $data): bool {
        dd('Add books function needs to still be writern');
    }

    /** Update book data, using our library classes.
     *      @param array $data Associative array with book data to update.
     *         Expected keys: id (int), title (string), writers (array), genres (array), offices (array)
     *      @return bool True on success, false on failure.
     */
    public function updateBook(array $data): bool {
        // Validate upfront
        if (empty($data['book_id']) || !is_numeric($data['book_id'])) {
            return false;
        }

        // Temporary handling until many-to-many offices are supported
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

    /** W.I.P. (potentially obsolete)
     * @return array
     */
    public function getOneForDisplay(int $id): array {
        $row = $this->books->findOne($id);

        return [
            'id'     => (int)$row['id'],
            'title'  => $row['title'],
            'genres' => $this->genres->getGenreNamesByBookId($id),
            'writers' => $this->writers->getWriterNamesByBookId($id),
            'status' => $this->status->getDisplayStatusByBookId($id),
        ];
    }
}