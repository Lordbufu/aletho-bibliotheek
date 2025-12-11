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
        $out = [
            'id'            => $book['id'],
            'title'         => $book['title'],
            'writers'       => $this->libs->writers()->getWriterNamesByBookId((int)$book['id']),
            'genres'        => $this->libs->genres()->getGenreNamesByBookId((int)$book['id']),
            'office'        => $this->libs->offices()->getOfficeNameByOfficeId((int)$book['home_office']),
            'curOffice'     => $this->libs->offices()->getOfficeNameByOfficeId((int)$book['cur_office']),
            'status'        => $this->libs->statuses()->getBookStatus((int)$book['id']),
            'dueDate'       => $this->libs->statuses()->getBookDueDate((int)$book['id']),
            'curLoaner'     => $this->libs->loaners()->getLoanersByBookId((int)$book['id'], 'current', 'Geen huidige lener', 1, true),
            'prevLoaners'   => $this->libs->loaners()->getLoanersByBookId((int)$book['id'], 'previous', 'Geen vorige leners', 5, true),
            'canEditOffice' => App::getService('auth')->canManageOffice($book['home_office']),
        ];

        return $out;
    }

    /** Helper: Cancel current DB transaction and return false  */
    protected function failTransaction(): bool {
        $this->db->cancelTransaction();
        return false;
    }

    /** API: Get all books as an array for views */
    public function getAllForDisplay(): array {
        $books = $this->libs->books()->findAllBooks();
        $out = [];

        foreach ($books as $book) {
            if (!$book['active']) {
                continue;
            }

            $out[] = $this->formatBookForDisplay($book);
        }

        return $out;
    }

    /** API: Get a specific book for views */
    public function getBookById(int $bookId): ?array {
        $book = $this->libs->books()->findOneBook($bookId);
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
        
        foreach($this->libs->books()->findAllBooks() as $book) {
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

    /** Helper & API: Figure out where a book should goto next */
    public function resolveReturnTarget(array $book, int $loanerOffice, string $statusType): int {
        return $this->libs->books()->resolveReturnTarget($book, $loanerOffice, $statusType);
    }

    /** API: Resolve the books transport state */
    public function resolveTransport(array $book, ?int $loanerOffice, ?string $statusType): bool {
        return $this->libs->books()->resolveTransport($book, $loanerOffice, $statusType);
    }

    /** API: Change all book status related data, and notify user/admin when required */
    // Mental note: @param $currentTrigger -> can be used to seperate user actions and cron jobs
    // TODO-LIST:
        // - Review potential database polution, and make sure old data is cleaned up properly
    public function changeBookStatus(int $bookId, int $requestedStatusId, string $currentTrigger, array $loaner = []): bool {
        $oldStatus      = $this->libs->statuses()->getBookStatus($bookId, 'id');
        $requestStatus  = $this->libs->statuses()->getStatusById($requestedStatusId) ?? [];
        $book           = $this->libs->books()->findOneBook($bookId);
        $newLoaner      = null;
        $eventKeyId     = null;
        $transport      = false;

        $this->db->startTransaction();

        try {
            /** LOANER HANDLING */
            if (!empty($loaner)) {
                $transport = $this->resolveTransport($book, $loaner['office'] ?? null, $requestStatus['type'] ?? null);
                $newLoaner = $this->libs->loaners()->findOrCreateByEmail($loaner['name'] ?? '', $loaner['email'], (int)($loaner['office'] ?? 0));

                if (!$newLoaner) {
                    return $this->failTransaction();
                }

                if (!$this->libs->loaners()->assignBookLoanerIfNeeded($bookId, $newLoaner, $requestedStatusId, $requestStatus)) {
                    return $this->failTransaction();
                }
            } else {
                if (!$this->libs->loaners()->deactivateBookLoanersIfNeeded($bookId, $requestStatus)) {
                    return $this->failTransaction();
                }
            }

            /** STATUS UPDATE */
            $statusUpdate = $this->libs->statuses()->updateBookStatus($bookId, $requestedStatusId, $transport);

            if (!$statusUpdate['record_id']) {
                return $this->failTransaction();
            }

            /** STATUS → EVENT LINKING */
            $eventKeyId = $this->libs->statuses()->linkEventIfNeeded($statusUpdate, $requestedStatusId, $oldStatus, $currentTrigger, $requestStatus);

            /** NOTIFICATIONS */
            if ($transport) {
                $admins = $this->libs->offices()->getAdminsForOffices($book['cur_office']);
                foreach ($admins as $admin) {
                    App::getService('notification')->dispatchStatusEvents(
                        $statusUpdate['final_status_id'],
                        $oldStatus,
                        [
                            ':book_name'        => $book['title']   ?? '',
                            ':user_name'        => $admin['name']   ?? '',
                            ':user_mail'        => $admin['email']  ?? '',
                            ':office'           => $this->libs->offices()->getOfficeNameByOfficeId($admin['office_id']),
                            ':due_date'         => $this->libs->statuses()->getBookDueDate((int)$book['id']),
                            'book_status_id'    => $statusUpdate['record_id'],
                            'noti_id'           => $eventKeyId,
                            'event'             => 'transport_request'
                        ]
                    );
                }
            } elseif ($newLoaner !== null) {
                App::getService('notification')->dispatchStatusEvents(
                    $statusUpdate['final_status_id'],
                    $oldStatus,
                    [
                        ':book_name'        => $book['title']   ?? '',
                        ':user_name'        => $loaner['name']  ?? '',
                        ':user_mail'        => $loaner['email'] ?? '',
                        ':office'           => $this->libs->offices()->getOfficeNameByOfficeId($book['cur_office']),
                        ':due_date'         => $this->libs->statuses()->getBookDueDate((int)$book['id']),
                        'book_status_id'    => $statusUpdate['record_id'],
                        'noti_id'           => $eventKeyId,
                        'event'             => 'transport_request'
                    ]
                );
            }
            $this->db->finishTransaction();
        } catch(\Throwable $t) {
            $this->db->cancelTransaction();
            throw $t;
        }
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

        /** Code i Compacted\Extracted:
            // if (!empty($loaner['office'])) {
            //     $targetOffice = $this->libs->books()->resolveReturnTarget($book, $loaner['office'], $requestStatus['type'] );

            //     if ($book['cur_office'] !== $targetOffice) {
            //         $transport = true;
            //     }
            // }

            // if (!empty($loaner['email'])) {
            //     $newLoaner = $this->libs->loaners()->findOrCreateByEmail($loaner['name'] ?? '', $loaner['email'], (int)($loaner['office'] ?? 0));

            //     if (!$newLoaner || empty($newLoaner['id'])) {
            //         $this->db->cancelTransaction();
            //         return false;
            //     }
            // }

            // if ($newLoaner && !in_array($requestedStatusId, [1, 3], true)) {
            //     $newDueDate = calculateDueDate(null, (int)($requestStatus['periode_length'] ?? 0));
            //     $result = $this->libs->loaners()->assignLoanerToBook($bookId, $newLoaner['id'], $requestedStatusId, $newDueDate);

            //     if (!$result) {
            //         return $this->db->cancelTransaction();
            //     }
            // }

            // if (($requestStatus['type'] ?? null) === 'Aanwezig') {
            //     $result = $this->libs->loaners()->deactivateActiveBookLoaners($bookId);
            //     if ($result === false) {
            //         $this->db->cancelTransaction();
            //         return false;
            //     }
            // }

            // $finalStatusId = $transport ? 3 : $requestedStatusId;

            // $statusResult = $this->libs->statuses()->setBookStatus($bookId, $finalStatusId);
            // if (!$statusResult) {
            //     $this->db->cancelTransaction();
            //     return false;
            // }

            // if ($requestedStatusId !== 1) {
            //     $eventStatusMap = App::getService('status')->getEventStatusMap();
            //     $eventKey       = $this->findEventKey($eventStatusMap, $finalStatusId, $oldStatus, $currentTrigger);
            //     $eventKeyId     = $this->libs->notifications()->getNotiIdByType($eventKey);

            //     if ($requestStatus) {
            //         $this->libs->statuses()->setStatusEvent($statusResult, $finalStatusId, $eventKeyId);
            //     }
            // }
        */