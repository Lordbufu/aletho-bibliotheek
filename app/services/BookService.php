<?php
namespace App\Services;

use App\Libs\Context\BookContext;
use App\App;

final class BookService {
    private \App\Database                   $db;
    private \App\Services\BookStatusService $bookStatus;
    private \App\Libs\BookRepo              $books;
    private \App\Libs\WritersRepo           $writers;
    private \App\Libs\GenresRepo            $genres;
    private \App\Libs\OfficesRepo           $offices;
    private \App\Libs\StatusRepo            $statuses;
    private \App\Services\LoanService       $loan;
    private \App\Libs\LoanerRepo            $loaner;

    public function __construct() {
        $this->db           = \App\App::getService('database');
        $this->bookStatus   = \App\App::getService('book_status');
        $this->books        = \App\App::getLibrary('books');
        $this->writers      = \App\App::getLibrary('writers');
        $this->genres       = \App\App::getLibrary('genres');
        $this->offices      = \App\App::getLibrary('offices');
        $this->statuses     = \App\App::getLibrary('status');
        $this->loan         = \App\App::getService('loan');
        $this->loaner       = \App\App::getLibrary('loaner');
    }

    /** Helper: Extract the correct status row for a single book */
    private function hydrateStatusForBook(int $bookId, array $rows): ?array {
        foreach ($rows as $row) {
            if ((int)$row['book_id'] !== $bookId) {
                continue;
            }

            $bsCtx          = $this->bookStatus->loadBookStatusContext($bookId);
            $currentName    = $this->getCurrentLoanerName($bsCtx->status['id'], $bookId);
            $previousList   = $this->getPreviousLoanerNames($bookId);
            $loanerHistory  = $this->buildLoanerHistory($currentName, $previousList);

            return [
                'statusId'      => (int)$row['status_id'],
                'type'          => $row['type'],
                'dueDate'       => $this->loan->getCurrentLoanById($bsCtx->status['id'], $bookId)?->endDate,
                'loanerHistory' => $loanerHistory
            ];
        }

        return null;
    }

    /** Helper: Combine the loaner history */
    private function buildLoanerHistory(string $current, array $previous): array {
        return array_merge([$current], $previous);
    }

    /** Helper: Fetch current loaner name or fallback string */
    private function getCurrentLoanerName(int $statusId, int $bookId): string {
        $loanCtx = $this->loan->getCurrentLoanById($statusId, $bookId);

        if (!$loanCtx?->loanerId) {
            return 'Geen Huidige Lener';
        }

        $loaner = $this->loaner->getLoanerById($loanCtx->loanerId);

        return $loaner?->name ?? 'Geen Huidige Lener';
    }

    /** Helper: Fetch previous loaner names or fallback string */
    private function getPreviousLoanerNames(int $bookId): array {
        $prevLoans = $this->loan->getPreviousLoansByBookId($bookId);
        $names = [];

        foreach ($prevLoans as $loan) {
            $loaner = $this->loaner->getLoanerById($loan->loanerId);
            if ($loaner) {
                $names[] = $loaner->name;
            }
        }

        return $names ?: ['Geen Vorige Leners'];
    }

    /** Facade: Return all active books (raw domain data only) */
    public function findAllActiveBooks(): array {
        return $this->books->findAllActiveBooks();
    }

    /** Facade: Find a book by exact title (CRUD-safe) */
    public function findBookByTitle(string $title): ?BookContext {
        return $this->books->findBookByTitle($title);
    }

    /** Facade: Fetch `books` context by book_id */
    public function findBookById(int $bookId): ?BookContext {
        return $this->books->findBookById($bookId);
    }

    /** Facade: Insert a new book record */
    public function insertBook(string $title, int $officeId): int {
        return $this->books->insertBook($title, $officeId);
    }

    /** Facade: Reactive (add) old book record */
    public function reactivateBook(int $bookId): void {
        $this->books->reactivateBook($bookId);
    }

    /** Facade: Deactivate (delete) book record */
    public function deactivateBook(int $bookId): void {
        $this->books->deactivateBook($bookId);
    }

    /** Facade: Update book title */
    public function updateBookTitle(int $bookId, string $title): void {
        $this->books->updateBookTitle($bookId, $title);
    }

    /** Facade: Set all offices for book */
    public function setAllBookOffices(int $bookId, int $officeId) {
        return $this->books->setAllBookOffices($bookId, $officeId);
    }

    /** Facade: Update office data for book */
    public function updateCurOffice(int $bookId, int $officeId): void {
        $this->books->updateCurBookOffice($bookId, $officeId);
    }

    /** Facade: Update reservation meta data */
    public function updateReservationDataForBook(int $bookId, array $data): void {
        $this->books->updateReservationDataForBook($bookId, $data);
    }

    /** API: Load all books for the catalog view */
    public function getBooksForView(): array {
        $books                  = $this->findAllActiveBooks();
        if (!$books) {
            return [];
        }

        $ids                    = array_map(fn(BookContext $b) => $b->id, $books);
        $writersMap             = $this->writers->getWritersForBooks($ids);
        $genresMap              = $this->genres->getGenresForBooks($ids);
        $activeStatusRows       = $this->statuses->getActiveStatuses($ids);

        $officeIds = [];
        foreach ($books as $b) {
            $officeIds[]        = $b->homeOfficeId;
            $officeIds[]        = $b->curOfficeId;
        }
        $officeNames            = $this->offices->getOfficeNamesForBooks($officeIds);

        foreach ($books as $b) {
            $b->writers         = $writersMap[$b->id] ?? [];
            $b->genres          = $genresMap[$b->id] ?? [];
            //4.1. Office names
            $b->homeOfficeName  = $officeNames[$b->homeOfficeId] ?? 'Onbekend';
            $b->curOfficeName   = $officeNames[$b->curOfficeId] ?? 'Onbekend';
            $b->status          = $this->hydrateStatusForBook($b->id, $activeStatusRows);
        }

        return $books;
    }

    /** API: Get data requested via XHR for the frontend JS suggestions feature */
    public function getBookFormData(string $type): array {
        switch ($type) {
            case 'writers':
                return $this->writers->getAllWriters();
            case 'genres':
                return $this->genres->getAllGenres();
            case 'offices':
                return $this->offices->getAllOffices();
            default:
                return [];
        }
    }

    /** API: Add or update a book (used by controller addBook) */
    public function addBook(array $input): ?int {
        $this->db->startTransaction();

        try {
            $officeName = $input['office'][0] ?? null;
            $office     = $this->offices->findByName($officeName);           
            $officeId   = (int)$office['id'];

            $existing   = $this->findBookByTitle($input['title']);

            if ($existing) {
                if (!$existing->active) {
                    $this->reactivateBook($existing->id);
                }
                $bookId = $existing->id;
            } else {
                $bookId = $this->insertBook(
                    $input['title'],
                    $officeId
                );
            }

            $writerIds = $this->writers->ensureWritersExist($input['writers']);
            $this->writers->syncBookWriters($bookId, $writerIds);

            $genreIds = $this->genres->ensureGenresExist($input['genres']);
            $this->genres->syncBookGenres($bookId, $genreIds);

            $this->bookStatus->finishActiveBookStatuses($bookId);
            $this->bookStatus->insertBookStatus($bookId, 1);

            $this->db->finishTransaction();

            return $bookId;
        } catch (\Throwable $t) {
            $this->db->cancelTransaction();
            error_log("[BookService] " . $t->getMessage());
            return null;
        }
    }

    /** API: Edit book data  */
    public function editBook(int $bookId, array $input): bool {
        $this->db->startTransaction();

        try {
            if (isset($input['title'])) {
                $this->updateBookTitle($bookId, $input['title']);
            }

            if (isset($input['office'][0])) {
                $officeName     = $input['office'][0];
                $office         = $this->offices->findByName($officeName);

                if (!$office) {
                    throw new \Exception("Unknown office: {$officeName}");
                }

                $this->setAllBookOffices($bookId, (int)$office['id']);
            }

            if (isset($input['writers'])) {
                $writerIds      = $this->writers->ensureWritersExist($input['writers']);
                $this->writers->syncBookWriters($bookId, $writerIds);
            }

            if (isset($input['genres'])) {
                $genreIds       = $this->genres->ensureGenresExist($input['genres']);
                $this->genres->syncBookGenres($bookId, $genreIds);
            }

            $this->db->finishTransaction();
            return true;
        } catch (\Throwable $t) {
            $this->db->cancelTransaction();
            error_log("[BookService] " . $t->getMessage());
            return false;
        }
    }

    /** API: Delete (deactivate) book */
    public function deleteBook(int $bookId): bool {
        $this->db->startTransaction();

        try {
            $this->bookStatus->finishActiveBookStatuses($bookId);
            $this->deactivateBook($bookId);

            $this->db->finishTransaction();
            return true;

        } catch (\Throwable $t) {
            $this->db->cancelTransaction();
            error_log("[BookService] " . $t->getMessage());
            return false;
        }
    }
}