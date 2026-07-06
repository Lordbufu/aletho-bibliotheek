<?php
namespace App\Services;

use App\Libs\Context\BookContext;
use App\ViewModel\BookViewModel;
use App\Libs\{BookRepo, BookStatusRepo, WriterRepo, GenreRepo, StatusRepo, LoanerRepo, LocationRepo, ReservationRepo};

// Re-factor status: tested and working
final class BookService {
    private \App\Database   $db;
    private BookRepo        $book;
    private BookStatusRepo  $bStatus;
    private WriterRepo      $writer;
    private GenreRepo       $genre;
    private LocationRepo    $location;
    private StatusRepo      $status;
    private LoanerRepo      $loaner;
    private ReservationRepo $reservation;

    public function __construct() {
        $this->db           = \App\App::getService('database');
        $this->book         = new BookRepo();
        $this->bStatus      = new BookStatusRepo();
        $this->writer       = new WriterRepo();
        $this->genre        = new GenreRepo();
        $this->location     = new LocationRepo();
        $this->status       = new StatusRepo();
        $this->loaner       = new LoanerRepo();
        $this->reservation  = new ReservationRepo();
    }

    /** Helper: Evaluate if book need to be added/reactivate or updated */
    private function classifyBooks(array $books, int $officeId): array {
        $result = [
            'duplicate' => null,
            'inactive'  => null,
            'active'    => null,
        ];

        foreach ($books as $book) {
            if ($book->book_home_loc === $officeId) {
                $result['duplicate'] = $book;
                break;
            }

            if (!$book->is_active) {
                $result['inactive'] = $book;
                continue;
            }

            $result['active'] = $book;
        }

        return $result;
    }

    /** Facade: Return all active books (raw domain data only) */
    public function findAllActiveBooks(): array {
        return $this->book->findAllActiveBooks();
    }

    /** Facade: Find a book by exact title (CRUD-safe) */
    public function findBooksByTitle(string $title): ?array {
        return $this->book->findBooksByTitle($title);
    }

    /** Facade: Reactive (add) old book record */
    public function reactivateBook(int $bookId): void {
        $this->book->reactivateBook($bookId);
    }

    /** Facade: Insert a new book record */
    public function insertBook(string $title, int $officeId): int {
        return $this->book->insertBook($title, $officeId);
    }

    /** Facade: Deactivate (delete) book record */
    public function deactivateBook(int $bookId): void {
        $this->book->deactivateBook($bookId);
    }

    /** Facade: Update book title */
    public function updateBookTitle(int $bookId, string $title): void {
        $this->book->updateBookTitle($bookId, $title);
    }

    /** Facade: Update/sync book locatie data */
    public function setAllBookOffices(int $book_id, int $officeId): void {
        $this->book->setAllBookOffices($book_id, $officeId);
    }

    /** API: Fetch all view related books data from the various repo's, and return a BookViewModel */
    public function getBooksForView(): ?array {
        $books          = $this->findAllActiveBooks();
        $bookIds        = array_column($books, 'book_id');

        $formatted  = BookViewModel::formatMany(
            $books,
            $this->writer->getBookWritersByBookId($bookIds),
            $this->genre->getBookGenresByBookId($bookIds),
            $this->location->getLocationNamesByIds($bookIds),
            $this->status->getBookStatusByBookId($bookIds),
            $this->loaner->getAllBookLoanersByBookId($bookIds),
            $this->reservation->checkReservationsForBookId($bookIds)
        );

        return $formatted;
    }

    /** API: Add or update a book (used by controller addBook) */
    public function addBook(array $input): int|array|null {
        $this->db->startTransaction();

        try {        
            $officeId   = (int)$input['office']['id'];
            $existing   = $this->findBooksByTitle($input['title']);
            $classification = $this->classifyBooks($existing, $officeId);

            if ($classification['duplicate']) {
                throw new \InvalidArgumentException("Dit boek bestaat al in deze locatie.");
            }

            if ($classification['inactive']) {
                $book = $classification['inactive'];
                $this->reactivateBook($book->book_id);
                $this->setAllBookOffices($book->book_id, $officeId);
                $bookId = $book->book_id;
            } else {
                $bookId = $this->insertBook($input['title'], $officeId);
            }

            $writerIds = $this->writer->resolveWriterIds($input['writers']);
            $this->writer->syncBookWriters($bookId, $writerIds);

            $genreIds = $this->genre->resolveGenreIds($input['genres']);
            $this->genre->syncBookGenres($bookId, $genreIds);

            $this->bStatus->deactiveBookStatus($bookId);
            $this->bStatus->setDefaultStatus($bookId);

            $this->db->finishTransaction();

            return [
                'valid'     => true,
                'book_id'   => $bookId
            ];
        } catch (\InvalidArgumentException $v) {
            $this->db->cancelTransaction();
            return [
                'valid'  => false,
                'errors' => $v->getMessage()
            ];
        } catch (\Throwable $t) {
            $this->db->cancelTransaction();
            error_log("[BookService] " . $t->getMessage());
            return ['valid'  => false];
        }
    }

    /** API: Edit book data  */
    public function editBook(int $bookId, array $input): ?array {
        $this->db->startTransaction();

        try {
            $book = $this->book->findBookById($bookId);
            if (!$book || !$book->is_active) {
                throw new \InvalidArgumentException("Boek is niet gevonden.");
            }

            // Ensure the correct dataset is used for claffifying if a book is duplicate or not.
            $officeId       = $input['office']['id'] ?? $book->book_home_loc;
            $title          = $input['title']        ?? $book->book_title;
            $existing       = $this->findBooksByTitle($title);
            $classification = $this->classifyBooks($existing, $officeId);

            if ($classification['duplicate'] && $classification['duplicate']->book_id !== $bookId) {
                throw new \InvalidArgumentException("Titel bestaat al in deze locatie.");
            }
    
            if (array_key_exists('title', $input)) {
                $this->updateBookTitle($bookId, $input['title']);
            }

            if (array_key_exists('office', $input)) {
                $loc = $this->location->getLocationContextById($officeId);
                if (!$loc || !$loc->is_active) {
                    throw new \InvalidArgumentException("Ongeldige locatie.");
                }

                $activeStatus = $this->bStatus->getActiveBookStatusForBook($bookId);
                if ($activeStatus && $activeStatus->status_id !== 1) {
                    throw new \InvalidArgumentException("Locatie kan niet worden veranderen tijdens een actieve status.");
                }

                $this->book->setAllBookOffices($bookId, $officeId);
            }

            if (array_key_exists('writers', $input)) {
                $writerIds = $this->writer->resolveWriterIds($input['writers']);
                $this->writer->syncBookWriters($bookId, $writerIds);
            }

            if (array_key_exists('genres', $input)) {
                $genreIds = $this->genre->resolveGenreIds($input['genres']);
                $this->genre->syncBookGenres($bookId, $genreIds);
            }

            $this->db->finishTransaction();
            return [ 'valid'  => true ];
        } catch (\InvalidArgumentException $v) {
            $this->db->cancelTransaction();
            return [
                'valid'  => false,
                'errors' => $v->getMessage()
            ];
        }  catch (\Throwable $t) {
            $this->db->cancelTransaction();
            error_log("[BookService] " . $t->getMessage());
            return [ 'valid'  => false ];
        }
    }

    /** API: Delete (deactivate) book */
    public function deleteBook(int $bookId): bool {
        $this->db->startTransaction();

        try {
            $this->bStatus->deactiveBookStatus($bookId);
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
    // Re-factored or old kept for legacy reasons code:
    /**  Old loaner history helper function */
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

    /** Facade: Fetch `books` context by book_id */
        // public function findBookById(int $bookId): ?BookContext {
        //     return $this->books->findBookById($bookId);
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