<?php
namespace App\Services;

use App\ViewModel\BookViewModel;
use App\Libs\{BookRepo, BookStatusRepo, WriterRepo, GenreRepo, StatusRepo, LoanerRepo, LocationRepo, ReservationRepo};

final class BookService {
    private BookRepo        $book;
    private BookStatusRepo  $bStatus;
    private WriterRepo      $writer;
    private GenreRepo       $genre;
    private LocationRepo    $location;
    private StatusRepo      $status;
    private LoanerRepo      $loaner;
    private ReservationRepo $reservation;

    public function __construct() {
        $this->book         = new BookRepo();
        $this->bStatus      = new BookStatusRepo();
        $this->writer       = new WriterRepo();
        $this->genre        = new GenreRepo();
        $this->location     = new LocationRepo();
        $this->status       = new StatusRepo();
        $this->loaner       = new LoanerRepo();
        $this->reservation  = new ReservationRepo();
    }

    /** Re-factored or potentially still working code snippet */
    // Re-factor status: tested and working
    /** Facade: Return all active books (raw domain data only) */
    public function findAllActiveBooks(): array {
        return $this->book->findAllActiveBooks();
    }

    // Re-factor status: tested and working
    /** API: Fetch all view related books data from the various repo's, and return a BookViewModel */
    public function getBooksForView(): ?array {
        $books          = $this->findAllActiveBooks();
        $bookIds        = array_column($books, 'book_id');

        $formatted  = BookViewModel::formatMany(
            $books,
            $this->writer->getBookWritersByBookId($bookIds),
            $this->genre->getBookGenresByBookId($bookIds),
            $this->location->getLocationNameByIds($bookIds),
            $this->status->getBookStatusByBookId($bookIds),
            $this->loaner->getAllBookLoanersByBookId($bookIds),
            $this->reservation->checkReservationsForBookId($bookIds)
        );

        return $formatted;
    }

    /** Helper: Extract the correct status row for a single book */
        // private function buildBookViewModel(BookContext $book, BookRelationsContext $rel = null, BookStatusContext $stat = null, array $officeNames ): BookViewModel {
        //     $vm                 = new BookViewModel();
        //     // Basic fields
        //     $vm->id             = $book->id;
        //     $vm->title          = $book->title;
        //     // Writers & genres
        //     $vm->writers        = $this->writers->namesFromIds($rel?->writerIds ?? []);
        //     $vm->genres         = $this->genres->namesFromIds($rel?->genreIds ?? []);
        //     // Offices
        //     $vm->office         = $officeNames[$book->homeOfficeId] ?? 'Onbekend';
        //     $vm->curOffice      = $officeNames[$book->curOfficeId] ?? 'Onbekend';
        //     // Status
        //     $vm->status         = $this->statuses->getStatusTypeName($stat?->statusId) ?? 'Onbekend';
        //     // Reservation
        //     $vm->isReserved     = $rel?->resvLoanerId !== null;
        //     // Due date
        //     $loan               = $this->loan->getCurrentLoanById($stat?->id, $book->id);
        //     $vm->dueDate        = $loan?->endDate?->format('Y-m-d') ?? 'Onbekend';
        //     // Loaner history
        //     $vm->loanerHistory  = $this->buildLoanerHistory($book->id, $loan);

        //     return $vm;
        // }

    /** Helper: Combine the loaner history */
        // private function buildLoanerHistory(int $bookId, ?LoanContext $currentLoan): array {
        //     $currentName = $currentLoan?->loanerId
        //         ? $this->loaner->getLoanerById($currentLoan->loanerId)?->name
        //         : 'Geen Huidige Lener';

        //     $previous = [];
        //     foreach ($this->loan->getPreviousLoansByBookId($bookId) as $loan) {
        //         $loaner = $this->loaner->getLoanerById($loan->loanerId);
        //         if ($loaner) {
        //             $previous[] = $loaner->name;
        //         }
        //     }

        //     if (!$previous) {
        //         $previous = ['Geen Vorige Leners'];
        //     }

        //     return array_merge([$currentName], $previous);
        // }

    /** Facade: Find a book by exact title (CRUD-safe) */
        // public function findBookByTitle(string $title): ?BookContext {
        //     return $this->books->findBookByTitle($title);
        // }

    /** Facade: Fetch `books` context by book_id */
        // public function findBookById(int $bookId): ?BookContext {
        //     return $this->books->findBookById($bookId);
        // }

    /** Facade: Insert a new book record */
        // public function insertBook(string $title, int $officeId): int {
        //     return $this->books->insertBook($title, $officeId);
        // }

    /** Facade: Reactive (add) old book record */
        // public function reactivateBook(int $bookId): void {
        //     $this->books->reactivateBook($bookId);
        // }

    /** Facade: Deactivate (delete) book record */
        // public function deactivateBook(int $bookId): void {
        //     $this->books->deactivateBook($bookId);
        // }

    /** Facade: Update book title */
        // public function updateBookTitle(int $bookId, string $title): void {
        //     $this->books->updateBookTitle($bookId, $title);
        // }

    /** Facade: Set all offices for book */
        // public function setAllBookOffices(int $bookId, int $officeId) {
        //     return $this->books->setAllBookOffices($bookId, $officeId);
        // }

    /** Facade: Update office data for book */
        // public function updateCurOffice(int $bookId, int $officeId): void {
        //     $this->books->updateCurBookOffice($bookId, $officeId);
        // }

    /** Facade: Update reservation meta data */
        // public function updateReservationDataForBook(int $bookId, array $data): void {
        //     $this->books->updateReservationDataForBook($bookId, $data);
        // }


    /** API: Get data requested via XHR for the frontend JS suggestions feature */
        // public function getBookFormData(string $type): array {
        //     switch ($type) {
        //         case 'writers':
        //             return $this->writers->getAllWriters();
        //         case 'genres':
        //             return $this->genres->getAllGenres();
        //         case 'offices':
        //             return $this->offices->getAllOffices();
        //         default:
        //             return [];
        //     }
        // }

    /** API: Add or update a book (used by controller addBook) */
        // public function addBook(array $input): ?int {
        //     $this->db->startTransaction();

        //     try {
        //         $officeName = $input['office'][0] ?? null;
        //         $office     = $this->offices->findOfficeByName($officeName);           
        //         $officeId   = (int)$office['id'];

        //         $existing   = $this->findBookByTitle($input['title']);

        //         if ($existing) {
        //             if (!$existing->active) {
        //                 $this->reactivateBook($existing->id);
        //             }
        //             $bookId = $existing->id;
        //         } else {
        //             $bookId = $this->insertBook(
        //                 $input['title'],
        //                 $officeId
        //             );
        //         }

        //         $writerIds = $this->writers->ensureWritersExist($input['writers']);
        //         $this->writers->syncBookWriters($bookId, $writerIds);

        //         $genreIds = $this->genres->ensureGenresExist($input['genres']);
        //         $this->genres->syncBookGenres($bookId, $genreIds);

        //         $this->bookStatus->finishActiveBookStatuses($bookId);
        //         $this->bookStatus->insertBookStatus($bookId, 1);

        //         $this->db->finishTransaction();

        //         return $bookId;
        //     } catch (\Throwable $t) {
        //         $this->db->cancelTransaction();
        //         error_log("[BookService] " . $t->getMessage());
        //         return null;
        //     }
        // }

    /** API: Edit book data  */
        // public function editBook(int $bookId, array $input): bool {
        //     $this->db->startTransaction();

        //     try {
        //         if (isset($input['title'])) {
        //             $this->updateBookTitle($bookId, $input['title']);
        //         }

        //         if (isset($input['office'][0])) {
        //             $officeName     = $input['office'][0];
        //             $office         = $this->offices->findOfficeByName($officeName);

        //             if (!$office) {
        //                 throw new \Exception("Unknown office: {$officeName}");
        //             }

        //             $this->setAllBookOffices($bookId, (int)$office['id']);
        //         }

        //         if (isset($input['writers'])) {
        //             $writerIds      = $this->writers->ensureWritersExist($input['writers']);
        //             $this->writers->syncBookWriters($bookId, $writerIds);
        //         }

        //         if (isset($input['genres'])) {
        //             $genreIds       = $this->genres->ensureGenresExist($input['genres']);
        //             $this->genres->syncBookGenres($bookId, $genreIds);
        //         }

        //         $this->db->finishTransaction();
        //         return true;
        //     } catch (\Throwable $t) {
        //         $this->db->cancelTransaction();
        //         error_log("[BookService] " . $t->getMessage());
        //         return false;
        //     }
        // }

    /** API: Delete (deactivate) book */
        // public function deleteBook(int $bookId): bool {
        //     $this->db->startTransaction();

        //     try {
        //         $this->bookStatus->finishActiveBookStatuses($bookId);
        //         $this->deactivateBook($bookId);

        //         $this->db->finishTransaction();
        //         return true;

        //     } catch (\Throwable $t) {
        //         $this->db->cancelTransaction();
        //         error_log("[BookService] " . $t->getMessage());
        //         return false;
        //     }
        // }
}

    // Re-factored code:
        // private function buildLoanerHistory(string $current, array $previous): array {
        //     return array_merge([$current], $previous);
        // }

        // public function getBooksForView(): array {
        //     $books                  = $this->findAllActiveBooks();
        //     if (!$books) {
        //         return [];
        //     }

        //     $ids                    = array_map(fn(BookContext $b) => $b->id, $books);
        //     $writersMap             = $this->writers->getWritersForBooks($ids);
        //     $genresMap              = $this->genres->getGenresForBooks($ids);
        //     $activeStatusRows       = $this->statuses->getActiveStatuses($ids);

        //     $officeIds = [];
        //     foreach ($books as $b) {
        //         $officeIds[]        = $b->homeOfficeId;
        //         $officeIds[]        = $b->curOfficeId;
        //     }
        //     $officeNames            = $this->offices->getOfficeNamesForBooks($officeIds);

        //     foreach ($books as $b) {
        //         $b->writers         = $writersMap[$b->id] ?? [];
        //         $b->genres          = $genresMap[$b->id] ?? [];
        //         //4.1. Office names
        //         $b->homeOfficeName  = $officeNames[$b->homeOfficeId] ?? 'Onbekend';
        //         $b->curOfficeName   = $officeNames[$b->curOfficeId] ?? 'Onbekend';
        //         $b->status          = $this->hydrateStatusForBook($b->id, $activeStatusRows);
        //     }

        //     return $books;
        // }

    /** Helper: Fetch current loaner name or fallback string */
        // private function getCurrentLoanerName(int $statusId, int $bookId): string {
        //     $loanCtx = $this->loan->getCurrentLoanById($statusId, $bookId);

        //     if (!$loanCtx?->loanerId) {
        //         return 'Geen Huidige Lener';
        //     }

        //     $loaner = $this->loaner->getLoanerById($loanCtx->loanerId);

        //     return $loaner?->name ?? 'Geen Huidige Lener';
        // }

    /** Helper: Fetch previous loaner names or fallback string */
        // private function getPreviousLoanerNames(int $bookId): array {
        //     $prevLoans = $this->loan->getPreviousLoansByBookId($bookId);
        //     $names = [];

        //     foreach ($prevLoans as $loan) {
        //         $loaner = $this->loaner->getLoanerById($loan->loanerId);
        //         if ($loaner) {
        //             $names[] = $loaner->name;
        //         }
        //     }

        //     return $names ?: ['Geen Vorige Leners'];
        // }