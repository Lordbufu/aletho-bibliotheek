<?php

namespace App;

use App\App;

class BooksService {
    protected \App\Libs\LoanersRepo $loaners;
    protected \App\Libs\StatusRepo  $status;
    protected \App\Libs\BookRepo    $books;
    protected \App\Libs\OfficeRepo  $offices;
    protected \App\Libs\GenreRepo   $genres;
    protected \App\Libs\WriterRepo  $writers;
    protected \App\Database         $db;

    /*  Construct all associated library classes, and logs it. */
    public function __construct() {
        try {
            $this->loaners = new \App\Libs\LoanersRepo();
            $this->status  = new \App\Libs\StatusRepo();
            $this->books   = new \App\Libs\BookRepo();
            $this->offices = new \App\Libs\OfficeRepo();
            $this->genres  = new \App\Libs\GenreRepo();
            $this->writers = new \App\Libs\WriterRepo();
            $this->db      = App::getService('database');
        } catch (\Throwable $t) {
            throw $t;
        }
    }

    /* Formate loaner data for display */
    protected function formatLoanersForDisplay(?array $loanersRaw, string $fallback = 'Geen leners'): array {
        if (empty($loanersRaw)) {
            return [$fallback];
        }

        // Normalize: if it's a single loaner (current), wrap it in an array
        $loaners = isset($loanersRaw[0]) ? $loanersRaw : [$loanersRaw];

        return array_map(fn($loaner) => $loaner['name'], $loaners);
    }

    /*  Get all books as an array, processed and formatted for views. */
    public function getAllForDisplay(): array {
        $books = $this->books->findAll();
        $out  = [];

        foreach ($books as $book) {
            if (!$book['active']) {
                continue;
            }

            $curLoanerRaw = $this->loaners->getCurrentLoanerByBookId((int)$book['id']);
            $prevLoanersRaw = $this->loaners->getPreviousLoanersByBookId((int)$book['id']);
        
            $out[] = [
                'id'     => $book['id'],
                'title'  => $book['title'],
                'writers' => $this->writers->getWriterNamesByBookId((int)$book['id']),
                'genres' => $this->genres->getGenreNamesByBookId((int)$book['id']),
                'office' => $this->offices->getOfficeNameByOfficeId((int)$book['home_office']),
                'curOffice' => $this->offices->getOfficeNameByOfficeId((int)$book['cur_office']),
                'status' => $this->status->getBookStatus((int)$book['id']),
                'dueDate' => $this->status->getBookDueDate((int)$book['id']),
                'curLoaner' => $this->formatLoanersForDisplay($curLoanerRaw, 'Geen huidige lener'),
                'prevLoaners' => $this->formatLoanersForDisplay($prevLoanersRaw, 'Geen vorige leners'),
                'canEditOffice' => App::getService('auth')->canManageOffice($book['home_office'])
            ];
        }

        return $out;
    }

    /* Get a specific book */
    public function getBookById(int $bookId): ?array {
        $book = $this->books->findOne($bookId);
        if (!$book || !$book['active']) {
            return null;
        }

        return [
            'id'     => $book['id'],
            'title'  => $book['title'],
            'writers' => $this->writers->getWriterNamesByBookId((int)$book['id']),
            'genres' => $this->genres->getGenreNamesByBookId((int)$book['id']),
            'office' => $this->offices->getOfficeNameByOfficeId((int)$book['home_office']),
            'curOffice' => $this->offices->getOfficeNameByOfficeId((int)$book['cur_office']),
            'status' => $this->status->getBookStatus((int)$book['id']),
            'dueDate' => $this->status->getBookDueDate((int)$book['id']),
        ];
    }

    /*  Get all writer names, for frontend autocomplete JQuery. */
    public function getWritersForDisplay(): array {
        $temp = $this->writers->getAllWriters();
        $out = [];
        
        foreach ($temp as $writer) {
            if (!$writer['active']) {
                continue;
            }

            $out[] = [
                'id' => $writer['id'],
                'name' => $writer['name']
            ];
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

            $out[] = [
                'id' => $genre['id'],
                'name' => $genre['name']
            ];
        }

        return $out;
    }

    /*  Add a new book, using our library classes. */
    public function addBook(array $data): mixed {
        $updated = false;

        $officeName = is_array($data['book_offices']) && count($data['book_offices']) > 0
            ? $data['book_offices'][0]
            : null;
        
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

        try {
            if (!$this->db->startTransaction()) {
                throw new \RuntimeException('Failed to start database transaction.');
            }

            if (!empty($data['book_name']) || !empty($data['book_offices'])) {
                $bookId = $this->books->addBook($data['book_name'], $officeId);
            }

            if (!empty($data['book_genres'])) {
                $this->genres->addBookGenres($data['book_genres'], $bookId);
            }

            if (!empty($data['book_writers'])) {
                $this->writers->addBookWriters($data['book_writers'], $bookId);
            }

            $this->status->setBookStatus($bookId , 1);

            $this->db->finishTransaction();

            return true;
        } catch(\Throwable $t) {
            $this->db->cancelTransaction();
            throw $t;
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
        } catch(\Throwable $t) {
            $this->db->cancelTransaction();
            throw $t;
            return false;
        }
    }

    /*  Disabled book by id. */
    public function disableBook(int $bookId): bool {
        return $this->books->disableBook($bookId);
    }

    /* */
    public function changeBookStatus($bookId, $statusId, $loaner): bool {
        $statusContext  = [];
        $book           = $this->getBookById($bookId);
        $this->db->startTransaction();

        try {
            $result = $this->status->setBookStatus($bookId, $statusId, $loaner, $statusContext);

        if (!$result) {
            error_log(sprintf(
                "[BooksService] Failed to update status for book_id=%d, status_id=%d, loaner_id=%s at %s",
                $bookId,
                $statusId,
                $loaner['id'] ?? 'null',
                (new \DateTimeImmutable())->format('Y-m-d H:i:s')
            ));

            return false;
        }

            $this->db->finishTransaction();
        } catch (\Throwable $t) {
            $this->db->cancelTransaction();
            throw $t;
        }

        dd('status set, now its notification time !!');
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

        return true;
    }
}