<?php
/** Temp mental note for status changes:
 *   // Manual status change events:
 *   'loan_confirm'          => ['status' => [2], 'from' => [1], 'trigger' => 'user_action', 'strict' => true],
 *   'pickup_ready_confirm'  => ['status' => [4], 'from' => [3], 'trigger' => 'user_action', 'strict' => true],
 *   'pickup_confirm'        => ['status' => [2], 'from' => [4], 'trigger' => 'user_action', 'strict' => true],
 *   'reserv_confirm'        => ['status' => [5], 'trigger' => 'user_action', 'strict' => false],
 *   'transport_request'     => ['status' => [3], 'trigger' => 'user_action', 'strict' => false],

 *   // Automated (logic driven) status change events:
 *   'reserv_confirm_auto'   => ['status' => [5], 'from' => [2], 'trigger' => 'auto_action', 'strict' => true],
 *   'transp_req_auto'       => ['status' => [3], 'from' => [2], 'trigger' => 'auto_action', 'strict' => true],

 *   // CRON status change events:
 *   'return_reminder'       => ['status' => [2], 'from' => [2], 'trigger' => 'cron_action', 'strict' => true],
 *   'overdue_reminder_user' => ['status' => [6], 'from' => [2], 'trigger' => 'cron_action', 'strict' => true],
 *   'overdue_notice_admin'  => ['status' => [6], 'from' => [2], 'trigger' => 'cron_action', 'strict' => true],
 */

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

    /** Helper: Cancel current DB transaction and return false  */
    protected function failTransaction(): bool {
        $this->db->cancelTransaction();
        return false;
    }

    /** Helper: Ensure all book related data is synced properly with the database */
    protected function updateBookMetadata(int $bookId, array $input): void {
        if (isset($input['genres'])) {
            $this->libs->genres()->syncBookGenres($bookId, $input['genres']);
        }

        if (isset($input['writers'])) {
            $this->libs->writers()->syncBookWriters($bookId, $input['writers']);
        }

        // TODO: Intended for future many-to-many office support if required.
        // if (isset($input['office'])) {
        //     $this->libs->offices()->syncBookOffices($bookId, $input['office']);
        // }
    }

// Basic API Library facade functions:
    /** API - Exact lookup methods ("find" functions):
     *      These functions return book records based on strict, exact database values.
     *      They are typically used by controllers when the required book(s) are known
     *      by ID or by fixed state (e.g., active/inactive).
     *
     *      - findSingleBook(int $id)
     *          Returns exactly one book by its ID.
     *
     *      - findSingleActiveBook(int $id)
     *          Returns one active book by ID. If the book exists but is inactive,
     *          it will not be returned.
     *
     *      - findAllBooks()
     *          Returns all books in the database, regardless of active state.
     *
     *      - findAllActiveBooks()
     *          Returns all books where `active = 1`.
     *
     *    These methods internally call BookRepo::findBooks() with exact-match filters.
     */
    public function findSingleBook(int $id) {
        return $this->libs->books()->findBooks(['id' => $id], true);
    }

    public function findSingleActiveBook(int $id) {
        return $this->libs->books()->findBooks(['id' => $id, 'active' => 1], true);
    }

    public function findAllBooks() {
        return $this->libs->books()->findBooks([], false);
    }

    public function findAllActiveBooks() {
        return $this->libs->books()->findBooks(['active' => 1], false);
    }

    /** API: Swap book active state by ID */
    public function swapBookActiveState(int $bookId): bool {
        return $this->libs->books()->swapBookActiveState($bookId);
    }

    /** API: Update `books`.`title` only */
    public function updateBookTitle(int $bookId, string $title): bool {
        return $this->libs->books()->updateBookTitle($bookId, $title);
    }

    // Shared book office update functions
    /** API: Update the `books`.`home_office` location */
    public function updateHomeOffice(int $bookId, int $officeId): bool {
        return $this->libs->books()->updateBookOffice($bookId, $officeId, 'home_office');
    }

    /** API: Update the `books`.`cur_office` location */
    public function updateCurrentOffice(int $bookId, int $officeId): bool {
        return $this->libs->books()->updateBookOffice($bookId, $officeId, 'cur_office');
    }

    /** API: Resolve the books transport state */
    public function resolveTransport(array $book, ?int $loanerOffice, ?string $statusType): bool {
        return $this->libs->books()->resolveTransport($book, $loanerOffice, $statusType);
    }

    /** API: Get all writer names, for frontend autocomplete JQuery */
    public function getWritersForDisplay(): array {
        return $this->libs->writers()->getWritersForDisplay();
    }

    /** API: Get all genre names, for frontend autocomplete JQuery */
    public function getGenresForDisplay(): array {
        return $this->libs->genres()->getGenresForDisplay();
    }

// API Advanced logic functions:
    /** API - Fuzzy lookup method ("search" function)
     *      This function performs partial-match (LIKE) searches on selected fields.
     *      It is intended for user-facing search features where the controller may
     *      receive incomplete or non-exact input.
     *
     *      - searchBooks(array $query)
     *          Accepts an associative array of optional search fields:
     *              [
     *                  'title'       => string,
     *                  'home_office' => string|int,
     *                  'cur_office'  => string|int
     *              ]
     *
     *          Only fields that are present and non-empty are included in the search.
     *          All comparisons use partial matching (SQL LIKE), allowing flexible
     *          search behavior such as:
     *              searchBooks(['title' => 'harry'])
     *              searchBooks(['home_office' => 3])
     *              searchBooks(['title' => 'ring', 'cur_office' => 2])
     *
     *          Returns an array of all matching books.
     */
    public function searchBooks(array $query = []): array {
        $allowed = ['title', 'home_office', 'cur_office', 'one'];
        $filters = [];

        foreach ($allowed as $field) {
            if (isset($query[$field]) && $query[$field] !== '') {
                $filters[$field] = $query[$field];
            }
        }

        return $this->libs->books()->searchBooks($filters);
    }

    /** API: Add a new book, or re-active if already in the DB, using our library classes */
    public function addBook(array $input): int {
        $existing = $this->searchBooks([
            'title' => $input['title'],
            'one' => true
        ]);

        $this->db->startTransaction();

        try {
            if ($existing) {
                $bookId = $existing['id'];

                if (!$existing['active']) {
                    $this->swapBookActiveState($bookId);
                }

                $this->updateBookMetadata($bookId, $input);
            } else {
                $officeId   = $this->libs->offices()->getOfficeIdByName($input['office']);
                $bookId     = $this->libs->books()->addBook($input['title'], $officeId);

                $this->updateBookMetadata($bookId, $input);
            }

            $this->libs->statuses()->setBookStatus($bookId, 1);     // Always set default `Aanwezig` status

            $this->db->finishTransaction();
            return $bookId;
        } catch (\Throwable $t) {
            error_log("[BooksService]" . $t->getMessage());
            $this->db->cancelTransaction();
            throw $t;
        }
    }

    /** API: Update book data, using our library classes */
    public function updateBook(array $input): int {
        $bookId         = (int) $input['id'];
        $officeId       = null;
        if (!empty($input['offices'])) {
            $officeId   = $this->libs->offices()->getOfficeIdByName($input['offices']);
        }

        try {
            $meta       = [];
            $this->db->startTransaction();

            if (!empty($input['title'])) {
                $this->updateBookTitle($bookId, $input['title']);
            }

            if ($officeId !== null) {
                // TODO: Re-factor these lines if office many to many office support has been added.
                $this->updateHomeOffice($bookId, $officeId);
                // $meta['offices'] = $input['offices'];
            }

            if (!empty($input['genres'])) {
                $meta['genres'] = $input['genres'];
            }

            if (!empty($input['writers'])) {
                $meta['writers'] = $input['writers'];
            }

            if (!empty($meta)) {
                $this->updateBookMetadata($bookId, $meta);
            }

            $this->db->finishTransaction();
            return $bookId;

        } catch (\Throwable $t) {
            error_log("[BooksService]" . $t->getMessage());
            $this->db->cancelTransaction();
            throw $t;
        }
    }

    /** API: Change all book status related data, and notify user/admin when required */
    // Mental note: @param $currentTrigger -> can be used to seperate user actions and cron jobs, but we are not using it yet.
    // TODO-LIST:
        // - Review potential database polution, and make sure old data is cleaned up properly
    public function changeBookStatus(int $bookId, int $requestedStatusId, string $currentTrigger, array $loaner = []): bool {
        $oldStatus      = (int)$this->libs->statuses()->getBookStatus($bookId, 'id');
        $requestStatus  = $this->libs->statuses()->getStatusById($requestedStatusId) ?? [];
        $book           = $this->findSingleActiveBook($bookId);
        $localTrigger   = $currentTrigger;
        $newLoaner      = null;
        $targetOffice   = null;
        $transport      = false;

        $this->db->startTransaction();

        try {
            /** LOANER HANDLING */
            if (!empty($loaner)) {
                $transport = $this->resolveTransport($book, $loaner['office'] ?? null, $requestStatus['type'] ?? null);
                $newLoaner = $this->libs->loaners()->findOrCreateByEmail($loaner['name'] ?? '', $loaner['email'], (int)($loaner['office'] ?? 0));

                // Set the correct trigger tag for event mapping for a logic driven trigger
                if ($transport) { $localTrigger = 'auto_action'; }

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

                error_log("[BooksService] Starting to evaluate the transport state and office update based on: oldStatus=$oldStatus, requestedStatusId=$requestedStatusId, cur_office={$book['cur_office']}, home_office={$book['home_office']}");

                if ($oldStatus == 3 && $requestedStatusId == 1) {
                    if ($book['cur_office'] !== $book['home_office']) {
                        $this->updateCurrentOffice($bookId, $book['home_office']);
                        $book = $this->findSingleActiveBook($bookId); // refresh
                        error_log("[BooksService] Updating book info after a office update for bookId=$bookId its cur_office={$book['home_office']}");
                        $targetOffice = $book['home_office'];
                    }
                }

                if ($requestedStatusId === 1) { // 1 = Aanwezig
                    // Only trigger transport if coming from a status that represents a return
                    if ((int)$oldStatus === 2) {
                        if ($book['cur_office'] !== $book['home_office']) {
                            $transport = true;
                            $localTrigger = 'auto_action';
                            $targetOffice = $book['home_office'];
                            error_log("[BooksService] Return flow: transport=$transport, trigger=$localTrigger, targetOffice=$targetOffice");
                        }
                    }
                }
            }

            /** STATUS UPDATE */
            $statusUpdate = $this->libs->statuses()->updateBookStatus($bookId, $requestedStatusId, $transport, $localTrigger);
            error_log("[BooksService] finalStatusId={$statusUpdate['finalStatusId']}");

            if ($statusUpdate['finalStatusId'] === 4) { // 4 = Ligt Klaar
                if (!empty($newLoaner) && $book['cur_office'] !== $newLoaner['office_id']) {
                    $this->updateCurrentOffice($bookId, $newLoaner['office_id']);
                    $book = $this->findSingleActiveBook($bookId);   // refresh
                    error_log("[BooksService] Updating book info after a office update for bookId=$bookId its cur_office={$book['home_office']}"); 
                    $targetOffice = $newLoaner['office_id'];            // Set the correct target office
                }
            }

            if ($requestedStatusId === 2 && !empty($newLoaner)) { // Afwezig
                $targetOffice = $newLoaner['office_id'];
            }

            if ($requestedStatusId === 5 && !empty($newLoaner)) { // Gereserveerd
                $targetOffice = $newLoaner['office_id'];
            }

            if ($transport && $targetOffice === null) { // Transport triggered by loaner request
                $targetOffice = $newLoaner['office_id'] ?? $book['home_office'];
            }

            if (!$statusUpdate['record_id']) {
                return $this->failTransaction();
            }

            /** STATUS → EVENT LINKING */
            $this->libs->statuses()->linkEventIfNeeded($statusUpdate, $requestedStatusId, $oldStatus, $localTrigger, $requestStatus);

            /** NOTIFICATIONS */
            if ($transport) {
                if ($targetOffice === null) {
                    $targetOffice = $book['cur_office'];
                }

                $admins = $this->libs->offices()->getAdminsForOffices($targetOffice);
                foreach ($admins as $admin) {
                    App::getService('notification')->dispatchStatusEvents(
                        $statusUpdate['finalStatusId'],
                        [
                            ':book_name'        => $book['title']   ?? '',
                            ':user_name'        => $admin['name']   ?? '',
                            ':user_mail'        => $admin['email']  ?? '',
                            ':office'           => $this->libs->offices()->getOfficeNameByOfficeId($targetOffice),
                            ':due_date'         => $this->libs->statuses()->getBookDueDate((int)$book['id']),
                            'book_status_id'    => $statusUpdate['record_id'],
                        ]
                    );
                }
            } elseif ($newLoaner !== null) {
                if ($targetOffice === null) {
                    $targetOffice = $book['cur_office'];
                }

                App::getService('notification')->dispatchStatusEvents(
                    $statusUpdate['finalStatusId'],
                    [
                        ':book_name'        => $book['title']   ?? '',
                        ':user_name'        => $loaner['name']  ?? '',
                        ':user_mail'        => $loaner['email'] ?? '',
                        ':office'           => $this->libs->offices()->getOfficeNameByOfficeId($targetOffice),
                        ':due_date'         => $this->libs->statuses()->getBookDueDate((int)$book['id']),
                        'book_status_id'    => $statusUpdate['record_id'],
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
}