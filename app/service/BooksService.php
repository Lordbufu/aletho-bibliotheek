<?php
namespace App\Service;

use App\App;

class BooksService {
    protected \App\Libraries    $libs;
    protected \App\Database     $db;

    public function __construct() {
        try {
            $this->libs = App::getLibraries();
            $this->db   = App::getService('database');
        } catch (\Throwable $t) {
            throw $t;
        }
    }

    /** Helper: Format book data for display */
    protected function formatBookForDisplay(array $book): array {
        $curLoanerRaw  = $this->libs->loaners()->getCurrentLoanerByBookId((int)$book['id']);
        $prevLoanersRaw = $this->libs->loaners()->getPreviousLoanersByBookId((int)$book['id']);

        $out = [
            'id'            => $book['id'],
            'title'         => $book['title'],
            'writers'       => $this->libs->writers()->getWriterNamesByBookId((int)$book['id']),
            'genres'        => $this->libs->genres()->getGenreNamesByBookId((int)$book['id']),
            'office'        => $this->libs->offices()->getOfficeNameByOfficeId((int)$book['home_office']),
            'curOffice'     => $this->libs->offices()->getOfficeNameByOfficeId((int)$book['cur_office']),
            'status'        => $this->libs->statuses()->getBookStatus((int)$book['id']),
            'dueDate'       => $this->libs->statuses()->getBookDueDate((int)$book['id']),
            'curLoaner'     => $this->formatLoanersForDisplay($curLoanerRaw, 'Geen huidige lener'),
            'prevLoaners'   => $this->formatLoanersForDisplay($prevLoanersRaw, 'Geen vorige leners'),
            'canEditOffice' => App::getService('auth')->canManageOffice($book['home_office']),
        ];

        return $out;
    }

    /** Helper: Format loaner data for display */
    protected function formatLoanersForDisplay(?array $loanersRaw, string $fallback = 'Geen leners'): array {
        if (empty($loanersRaw)) {
            return [$fallback];
        }

        $loaners = isset($loanersRaw[0]) ? $loanersRaw : [$loanersRaw];

        return array_map(fn($loaner) => $loaner['name'], $loaners);
    }

    /** Get all books as an array, processed and formatted for views */
    public function getAllForDisplay(): array {
        $books = $this->libs->books()->findAll();
        $out = [];

        foreach ($books as $book) {
            if (!$book['active']) {
                continue;
            }

            $out[] = $this->formatBookForDisplay($book);
        }

        return $out;
    }

    /** Get a specific book */
    public function getBookById(int $bookId): ?array {
        $book = $this->libs->books()->findOne($bookId);
        if (!$book || !$book['active']) {
            return null;
        }

        return $this->formatBookForDisplay($book);
    }

    /** Swap book active state by ID */
    public function swapBookActiveState(int $bookId): bool {
        return $this->libs->books()->swapBookActiveState($bookId);
    }

    /** Add a new book, using our library classes. */
    public function addBook(array $data): mixed {
        $updated = false;

        $officeName = is_array($data['book_offices']) && count($data['book_offices']) > 0
            ? $data['book_offices'][0]
            : null;
        
        foreach($this->libs->books()->findAll() as $book) {
            if ($book['title'] === $data['book_name']) {
                if ($book['active']) {
                    return "Deze naam staat al in de database, en is nu weer actief.";
                }

                $this->swapActiveState($book['id']);

                return true;
            }
        }

        $officeId = $this->libs->offices()->getOfficeIdByName($officeName);

        try {
            if (!$this->db->startTransaction()) {
                throw new \RuntimeException('Failed to start database transaction.');
            }

            if (!empty($data['book_name']) || !empty($data['book_offices'])) {
                $bookId = $this->libs->books()->addBook($data['book_name'], $officeId);
            }

            if (!empty($data['book_genres'])) {
                $this->libs->genres()->addBookGenres($data['book_genres'], $bookId);
            }

            if (!empty($data['book_writers'])) {
                $this->libs->writers()->addBookWriters($data['book_writers'], $bookId);
            }

            $this->libs->statuses()->setBookStatus($bookId , 1);

            $this->db->finishTransaction();

            return true;
        } catch(\Throwable $t) {
            $this->db->cancelTransaction();
            throw $t;
            return false;
        }
    }

    /** Update book data, using our library classes. */
    public function updateBook(array $data): bool {
        if (empty($data['book_id']) || !is_numeric($data['book_id'])) {
            return false;
        }

        $officeId = is_array($data['book_offices']) && count($data['book_offices']) > 0
            ? $data['book_offices'][0]
            : null;

        try {
            if (!$this->db->startTransaction()) {
                throw new \RuntimeException('Failed to start database transaction.');
            }

            if (!empty($data['book_name'])) {
                $this->libs->books()->updateBookTitle($data['book_id'], $data['book_name']);
            }

            if (!empty($data['book_offices'])) {
                $this->libs->books()->updateBookOffice($data['book_id'], $officeId);
            }

            if (!empty($data['book_genres'])) {
                $this->libs->genres()->updateBookGenres($data['book_id'], $data['book_genres']);
            }

            if (!empty($data['book_writers'])) {
                $this->libs->writers()->updateBookWriters($data['book_id'], $data['book_writers']);
            }

            $this->db->finishTransaction();

            return true;
        } catch(\Throwable $t) {
            $this->db->cancelTransaction();
            throw $t;
            return false;
        }
    }

    /** Change all book status related data, and notify user/admin when required */
    public function changeBookStatus(int $bookId, int $statusId, array $loaner = []): bool {
        $statusContext  = [];
        $this->db->startTransaction();

        // dd("changeBookStatus function rework ongoing!");
        try {
            $result = $this->libs->statuses()->setBookStatus($bookId, $statusId, $loaner, $statusContext);

            dd($result);
            if (!$result) {
                error_log(sprintf(
                    "[BooksService] Failed to update status for book_id=%d, status_id=%d at %s",
                    $bookId,
                    $statusId,
                    (new \DateTimeImmutable())->format('Y-m-d H:i:s')
                ));
                $this->db->cancelTransaction();
                return false;
            }

            $this->db->finishTransaction();
        } catch (\Throwable $t) {
            $this->db->cancelTransaction();
            throw $t;
        }

        dd('Status set, now its notification time !!');

        return true;
    }

    // Facade links to library functions without a specific service
    /** Get all writer names, for frontend autocomplete JQuery. */
    public function getWritersForDisplay(): array {
        return $this->libs->writers()->getWritersForDisplay();
    }

    /** Get all genre names, for frontend autocomplete JQuery. */
    public function getGenresForDisplay(): array {
        return $this->libs->genres()->getGenresForDisplay();
    }
}

    // $eventContext = [
    //     ':book_name'    => $book['title'],
    //     ':user_name'    => $loaner['name'],
    //     ':user_mail'    => $loaner['email'],
    //     ':user_office'  => $loaner['office_id'],
    //     ':due_date'     => $book['dueDate'],
    //     ':book_office'  => $book['office'],
    //     // ':action_intro' => 'Het is ons opgevallen dat je dit boek kan verlengen, mocht je daar belang bij hebben.',
    //     // ':action_link'  => 'https://biblioapp.nl/',
    //     // ':action_label' => 'Boek Verlengen'
    // ];

    // // Trigger notifications after commit (or via an async job)
    // $this->dispatchStatusEvents($statusId, $book, $loaner, $eventContext);

    // /*  Disabled book by id. */
    // public function disableBook(int $bookId): bool {
    //     return $this->books->disableBook($bookId);
    // }