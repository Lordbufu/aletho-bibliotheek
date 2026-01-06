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

// Helper functions:
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

        // TODO: Re-factor these lines if office many to many office support has been added.
        // if (isset($input['office'])) {
        //     $this->libs->offices()->syncBookOffices($bookId, $input['office']);
        // }
    }

    /** Helper: Determine if the book's current office should be updated */
    protected function shouldUpdateOffice(array $book, int $targetOffice): bool {
        return $targetOffice !== null && $book['cur_office'] !== $targetOffice;
    }

    /** Helper: Handle loaner context during status change */
    protected function handleLoanerContext(array $book, int $bookId, int $requestedStatusId, array $requestStatus, array $loaner): array {
        if (empty($loaner)) {
            $this->libs->loaners()->deactivateBookLoanersIfNeeded($bookId, $requestStatus);
            return [false, null, null];
        }
        
        $transport = $this->resolveTransport($book, $loaner['office'] ?? null, $requestStatus['type'] ?? null);
        $newLoaner = $this->libs->loaners()->findOrCreateByEmail($loaner['name'] ?? '', $loaner['email'], (int)($loaner['office'] ?? 0));
        
        if (!$newLoaner) {
            return [false, null, null];
        }
        
        $assigned = $this->libs->loaners()->assignBookLoanerIfNeeded($bookId, $newLoaner, $requestedStatusId, $requestStatus);
        
        if (!$assigned) {
            return [false, null, null];
        }
        
        $targetOffice = null;

        return [$transport, $newLoaner, $targetOffice];
    }

    /** Helper: Build notification payload for status change events */
    protected function buildNotificationPayload(array $book, int $targetOffice, int $recordId, array $user): array {
        return [
            ':book_name' => $book['title'] ?? '',
            ':user_name' => $user['name'] ?? '',
            ':user_mail' => $user['email'] ?? '',
            ':office' => $this->libs->offices()->getOfficeNameByOfficeId($targetOffice),
            ':due_date' => $this->libs->statuses()->getBookDueDate((int)$book['id']),
            'book_status_id' => $recordId,
        ];
    }

    /** Helper: Determine the target office based on status update and conditions */
    protected function determineTargetOffice(array $statusUpdate, int $requestedStatusId, array $book, ?array $newLoaner, bool $transport): ?int {
        if ($statusUpdate['finalStatusId'] === 4 && !empty($newLoaner)) {
            if ($book['cur_office'] !== $newLoaner['office_id']) {
                $this->updateCurrentOffice($book['id'], $newLoaner['office_id']);
            }

            return $newLoaner['office_id'];
        }

        if (!empty($newLoaner) && in_array($requestedStatusId, [2, 5], true)) {
            return $newLoaner['office_id'];
        }

        if ($transport) {
            return $newLoaner['office_id'] ?? $book['home_office'] ?? $book['cur_office'];
        }

        if (!empty($newLoaner)) {
            return $book['cur_office'];
        }

        return null;
    }

    /** Helper: Send notifications based on status update context */
    protected function sendNotifications(array $statusUpdate, array $book, ?int $targetOffice, bool $transport, ?array $newLoaner, array $loanerInput): void {
        // Transport → notify admins 
        if ($transport) {
            $admins = $this->libs->offices()->getAdminsForOffices($targetOffice);
            foreach ($admins as $admin) {
                App::getService('notification')->dispatchStatusEvents(
                    $statusUpdate['finalStatusId'],
                    $this->buildNotificationPayload($book, $targetOffice, $statusUpdate['record_id'], $admin)
                );
            }
            return;
        }
        
        // Loaner present → notify loaner
        if ($newLoaner !== null) {
            App::getService('notification')->dispatchStatusEvents(
                $statusUpdate['finalStatusId'],
                $this->buildNotificationPayload($book, $targetOffice, $statusUpdate['record_id'], $loanerInput)
            );
        }
    }

// API Basic logic functions:
    /** API - Exact lookup methods ("find" functions) */
    // Potentially useless
    public function findSingleBook(int $id) {
        return $this->libs->books()->findBooks(['id' => $id], true);
    }

    public function findSingleActiveBook(int $id) {
        return $this->libs->books()->findBooks(['id' => $id, 'active' => 1], true);
    }

    // Potentially useless
    public function findAllBooks() {
        return $this->libs->books()->findBooks([], false);
    }

    public function findAllActiveBooks() {
        return $this->libs->books()->findBooks(['active' => 1], false);
    }

    /** API: Update the `books`.`home_office` location */
    public function updateHomeOffice(int $bookId, int $officeId): bool {
        return $this->libs->books()->updateBookOffice($bookId, $officeId, 'home_office');
    }

    /** API: Update the `books`.`cur_office` location */
    public function updateCurrentOffice(int $bookId, int $officeId): bool {
        return $this->libs->books()->updateBookOffice($bookId, $officeId, 'cur_office');
    }

    /** API: Swap book active state by ID */
    public function swapBookActiveState(int $bookId): bool {
        return $this->libs->books()->swapBookActiveState($bookId);
    }

    /** API: Update `books`.`title` only */
    public function updateBookTitle(int $bookId, string $title): bool {
        return $this->libs->books()->updateBookTitle($bookId, $title);
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

    /** API - Fuzzy lookup method ("search" function) */
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
            [$transport, $newLoaner, $targetOffice] = $this->handleLoanerContext($book, $bookId, $requestedStatusId, $requestStatus, $loaner);
            
            if ($transport) {
                $localTrigger = 'auto_action';
            }

            $statusUpdate = $this->libs->statuses()->updateBookStatus($bookId, $requestedStatusId, $transport, $localTrigger);

            if (!$statusUpdate['record_id']) {
                return $this->failTransaction();
            }

            $targetOffice = $this->determineTargetOffice($statusUpdate, $requestedStatusId, $book, $newLoaner, $transport);

            if ($this->shouldUpdateOffice($book, $targetOffice)) {
                $this->updateCurrentOffice($bookId, $targetOffice);
                $book = $this->findSingleActiveBook($bookId);
            }

            $this->libs->statuses()->linkEventIfNeeded($statusUpdate, $requestedStatusId, $oldStatus, $localTrigger, $requestStatus);

            $this->sendNotifications($statusUpdate, $book, $targetOffice, $transport, $newLoaner, $loaner);

            $this->db->finishTransaction();
        } catch(\Throwable $t) {
            $this->db->cancelTransaction();
            throw $t;
        }

        return true;
    }
}