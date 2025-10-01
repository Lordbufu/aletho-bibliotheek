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

    /**
     * Construct all associated library classes.
     * And log what was constructed as part of service log warnings.
     */
    public function __construct() {
        $this->books   = new BookRepo();
        $this->genres  = new GenreRepo();
        $this->writers = new WriterRepo();
        $this->status  = new StatusRepo();
        $this->offices = new OfficeRepo();

        App::getService('logger')->info(
            "Service 'books' has constructed 'BookRepo', 'GenreRepo', 'WriterRepo', 'StatusRepo' and 'OfficeRepo'",
            'bookservices'
        );
    }

    /**
     * Get all books as an array, processed and formatted for views.
     * 
     * @return array
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
                'office' => $this->offices->getOfficeNamesById((int)$row['office_id']),
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

    /**
     * Get all writer names, for frontend autocomplete JQuery.
     * 
     * @return array
     */
    public function getWritersForDisplay(): array {
        $temp = $this->writers->getAllWriters();
        $out = [];
        
        foreach ($temp as $writer) {
            $out[] = $writer['name'];
        }

        return $out;
    }

    /**
     * Get all genre names, for frontend autocomplete JQuery.
     * 
     * @return array
     */
    public function getGenresForDisplay(): array {
        $temp = $this->genres->getAllGenres();
        $out = [];

        foreach ($temp as $genre) {
            $out[] = $genre['name'];
        }

        return $out;
    }

    /**
     * Get all office names, for frontend autocomplete JQuery.
     * 
     * @return array
     */
    public function getOfficesForDisplay(): array {
        $temp = $this->offices->getAllOffices();
        $out = [];

        foreach ($temp as $office) {
            $out[] = $office['name'];
        }

        return $out;
    }

    /**
     * W.I.P. Update book data, using our library classes.
     */
    public function updateBook(int $id, array $data) {
        // Seperate data for the libraries
        $writData = $data['writers'] ?? [];
        $genData  = $data['genres'] ?? [];
        $offData  = $data['offices'] ?? [];
        $title    = $data['title'] ?? '';

        // Check $title and update book title via BookRepo
        $newTitle = $this->books->updateBookTitle($id, $title);
        // Check $genData and update genres via GenreRepo
        $newGenres = $this->genres->updateBookGenres($id, $genData);
        // Check $writData and update writers via WriterRepo
        $newWriters = $this->writers->updateBookWriters($id, $writData);
        // Update offices via OfficeRepo
        $newOffices = $this->office->updateBookOffices($id, $offData);

        // Return something meaningfull for user feedback, if all data was processed correctly.
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