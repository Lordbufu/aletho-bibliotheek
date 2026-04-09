<?php
namespace App\Services;

use App\Engine\{BookStatusEngine, TransitionContext};
use App\Engine\Result\TransitionResult;
use App\Engine\Instructions\{StatusChangeInstruction, LoanChangeInstruction, OfficeChangeInstruction, NotificationInstruction};
use App\Libs\{BookStatusRepo, MailNotificationRepo};
use App\Libs\Context\{BookContext, BookStatusContext, StatusContext, NotificationContext};
use App\Libs\Types\StatusType;
use App\App;

final class BookStatusService {
    private \App\Database           $db;
    private BookStatusRepo          $bookStatusRepo;
    private MailNotificationRepo    $notifications;
    private const                   ADMIN_RELEVANT_TYPES = ['transport_request', 'overdue_reminder_user',];


    public function __construct() {
        $this->db               = \App\App::getService('database');
        $this->bookStatusRepo   = new BookStatusRepo();
        $this->notifications    = new MailNotificationRepo();
    }

    /** Helper: Apply status change for the Transition flow */
    private function applyStatusChange(StatusChangeInstruction $instr, int $bookId): void {
        if ($instr->newStatusType === StatusType::GERESERVEERD) {
            App::getService('books')->updateReservationDataForBook(
                $bookId,
                [
                    'resv_loaner_id' => $instr->reservationLoanerId,
                    'resv_office_id' => $instr->reservationLoanerOfficeId,
                    'resv_created_at' => date('Y-m-d H:i:s'),
                    'resv_expires_at' => null, // for now
                ]
            );
            return;
        }

        if ($instr->existingBookStatusId) {
            $this->deactiveBookStatus($instr->existingBookStatusId);
        }

    
        $this->createStatus($bookId, $instr->newStatusType, $instr->active, $instr->actionToken, $instr->tokenExpires);
    }

    /** Helper: Apply loan changes for the Transition flow */
    private function applyLoanChanges(LoanChangeInstruction $instr, BookContext $book): void {
        if ($instr->statusId === null) {
            // Nothing to update
            return;
        }

        // 1. Update existing loan row
        if ($instr->existingLoanRowId) {
            App::getService('loan')->updateLoan(
                $instr->existingLoanRowId,
                $instr->statusId,
                $instr->startDate,
                $instr->endDate,
                $instr->active
            );
            return;
        }

        // 2. Clean up previous loans if not done via the above update
        if ($instr->recyleLoanRowId) {
            App::getService('loan')->deactivateLoan($instr->recyleLoanRowId);
        }
        
        // 3. Create new loan row
        App::getService('loan')->createLoan(
            $book->id,
            $instr->loanerId,
            $instr->statusId,
            $instr->startDate,
            $instr->endDate,
            $instr->active
        );

        // 4. Clean up reservations if applicable during a transition to `Ligt Klaar`
        if ($book->resvLoanerId !== null && StatusType::fromId($instr->statusId) === StatusType::LIGT_KLAAR) {
            App::getService('books')->updateReservationDataForBook(
                $book->id,
                [   'resv_loaner_id' => null,
                    'resv_office_id' => null,
                    'resv_created_at' => null,
                    'resv_expires_at' => null
                ]
            );
        }
    }

    private function applyOfficeChanges(OfficeChangeInstruction $instr, int $bookId): void {
        App::getService('books')->updateCurOffice($bookId, $instr->newOfficeId);
    }

    /** Helper: Apply notification changes for the Transition flow */
    private function applyNotifications(NotificationInstruction $instr, int $bookStatusId, int $statusId): void {
        // 1. Resolve notification type → notification_id
        $notification = $this->notifications->getNotificationByType($instr->type);

        if (!$notification) {
            throw new \RuntimeException("Unknown notification type: {$instr->type}");
        }

        // 2. Insert into status_noti
        $this->notifications->linkStatusNotification(
            $bookStatusId,
            $statusId,
            $notification['id']
        );
    }

    /** Helper: Check if book transport is required */
    private function requiredOfficeForStatus(TransitionContext $tx, StatusContext $statusCtx): ?int {
        switch ($statusCtx->type) {
            case StatusType::AFWEZIG:
                return $tx->currentLoaner->officeId;
            case StatusType::AANWEZIG:
                $currentType = $tx->bookStatus->status['type'];

                // Special case: Return reservation office if transtion came from either Afwezig or Overdatum
                if (($currentType === StatusType::AFWEZIG || $currentType === StatusType::OVERDATUM)
                        && $tx->book->resvLoanerId !== null ) {
                    return $tx->book->resvOfficeId;
                }

                // Special case: Ensure a `Transport` -> `Aanwezig` flow does not trigger another office change
                if ($currentType === StatusType::TRANSPORT) {
                    return null;
                }

                // Normal return to home office flow
                return $tx->book->homeOfficeId;
            default:
                return null;
        }
    }

    // TODO: Consider relocating this to the correct domain, feels wrong to have it in the book_status domain, need to consult AI about that.
    // TODO: Consider extracting more logic to helpers as well, the function became a bit of conditional spagetti while fleshing it out fully.
    /** Helper: Build context required for notifications, based on the engine its transition result */
    private function buildNotificationContext(TransitionResult $result): NotificationContext {
        $bookCtx        = '';
        $loanerCtx      = '';
        $bookId         = null;

        // 1. Loan changes may include a bookId (loan creation, pickup, return)
        if ($result->loanChanges?->bookId) {
            $bookId = $result->loanChanges->bookId;
        }

        // 2. Status changes always include existingBookStatusId, which can be used to fetch the bookId
        if ($bookId === null && $result->statusChanges?->existingBookStatusId) {
            $statusRow = App::getService('book_status')->getBookIdForRow(
                $result->statusChanges->existingBookStatusId
            );
            $bookId = $statusRow;
        }

        // 3. Now fetch the book context
        if ($bookId !== null) {
            $bookCtx = App::getService('books')->findBookById($bookId);
        }

        // 2. Fetch notification row (id + template_id)
        $notiRow                = $this->notifications->getNotificationByType($result->notifications->type);
        if (!$notiRow) {
            throw new \RuntimeException("Unknown notification type: {$result->notifications->type}");
        }

        // 3. Build context
        $ctx                    = new NotificationContext();
        $ctx->notiType          = $result->notifications->type;                         // public string   $notiType;
        $ctx->notificationId    = (int)$notiRow['id'];                                  // public int      $notificationId;
        $ctx->bookStatusId      = (int)$result->statusChanges->existingBookStatusId;    // public int      $bookStatusId;

        $ctx->bookName          = $bookCtx->title;                                      // public string   $bookName;

        // 3.1 Build Loaner data
        if ($result->loanChanges?->loanerId !== null) {
            $loanerCtx          = App::getService('loaner')->getLoanerById($result->loanChanges->loanerId);
            $ctx->loanerName    = $loanerCtx->name;                                     // public string   $loanerName;
            $ctx->loanerEmail   = $loanerCtx->email;                                    // public string   $loanerEmail;
        } elseif ($result->notifications?->loanerId !== null) {
            $loanerCtx          = App::getService('loaner')->getLoanerById($result->notifications->loanerId);
            $ctx->loanerName    = $loanerCtx->name;                                     // public string   $loanerName;
            $ctx->loanerEmail   = $loanerCtx->email;                                    // public string   $loanerEmail;
        }

        // 4. Optional properties
        if ($result->loanChanges?->endDate) {                                           // public ?string  $dueDate        = null;
            $ctx->dueDate       = $result->loanChanges->endDate->format('d-m-Y');
        } elseif ($result->loanChanges?->statusId !== null && $result->loanChanges?->bookId !== null) {
            $loan = App::getService('loan')->getCurrentLoanById(
                $result->loanChanges->statusId,
                $result->loanChanges->bookId
            );
            $ctx->dueDate       = $loan->endDate?->format('d-m-Y');
        }

        // Always use the book's current office after the transition
        if ($bookCtx?->curOfficeId !== null) {
            $ctx->officeName = App::getService('offices')->getOfficeName(               // public ?string  $officeName     = null;
                $bookCtx->curOfficeId
            );
        }

        // if ($result->officeChanges !== null) {
        //     $ctx->officeName    = App::getService('offices')->getOfficeName(            // public ?string  $officeName     = null;
        //         $result->officeChanges->newOfficeId
        //     );
        // } elseif ($result->loanChanges?->bookId !== null) {
        //     $ctx->officeName    = App::getService('offices')->getOfficeName(            // public ?string  $officeName     = null;
        //         $bookCtx->curOfficeId
        //     );
        // }

        if ($result->statusChanges?->actionToken !== null) {
            $tokensData         = $this->getActionDataForRow($ctx->bookStatusId);
            $ctx->actionType    = $tokensData['action_type'];                           // public ?string  $actionType     = null;
            $ctx->actionToken   = $tokensData['action_token'];                          // public ?string  $actionToken    = null;
        }

        if (in_array($ctx->notiType, self::ADMIN_RELEVANT_TYPES, true)) {
            $originOfficeId = $result->notifications->originOfficeId;

            $originOffice = App::getService('offices')->getOfficeName($originOfficeId);
            $admin        = App::getService('user')->findAdminByOfficeId($originOfficeId);

            $ctx->adminName   = $admin->username;
            $ctx->adminEmail  = $admin->email;
            $ctx->adminOffice = $originOffice;
        }

        return $ctx;
    }

    /** Facade: Fetch action data based on row id */
    public function getActionDataForRow(int $id): ?array {
        return $this->bookStatusRepo->getActionDataForRow($id);
    }

    /** Facade: Get book_id based on the row index */
    public function getBookIdForRow($id): ?int {
        return $this->bookStatusRepo->getBookIdForRow($id);
    }

    /** Facade: Insert a new status row */
    public function insertBookStatus(int $bookId, int $statusId): int {
        return $this->bookStatusRepo->insertBookStatus($bookId, $statusId);
    }

    /** Facade: Mark all active statuses for a book as finished */
    public function finishActiveBookStatuses(int $bookId) {
        return $this->bookStatusRepo->finishActiveBookStatuses($bookId);
    }

    /** Facade: Deactive specific book_status row */
    public function deactiveBookStatus(int $bookStatusId) {
        return $this->bookStatusRepo->deactiveBookStatus($bookStatusId);
    }

    /** Facade: Create new book_status row */
    public function createStatus(int $bookId, string $statusType, bool $active = true, ?string $actionToken = null, ?\DateTimeImmutable $tokenExpires = null): int {
        return $this->bookStatusRepo->createStatus($bookId, $statusType, $active, $actionToken, $tokenExpires);
    }

    /** API: Load the full `BookStatusContext` based on a books its id */
    public function loadBookStatusContext($bookId): ?BookStatusContext {
        // 1. Load the associated `books` data
        $bookCtx = App::getService('books')->findBookById($bookId);
        if (!$bookCtx) {
            throw new \RuntimeException("Book not found");
        }

        // 2. Load the associated `book_status` data
        $bsCtx = $this->bookStatusRepo->getActiveStatusForBook($bookCtx->id);
        if (!$bsCtx) {
            throw new \RuntimeException("No active book status rows found");
        }

        // 3. Load the associated status context
        $statusCtx = App::getService('statuses')->getStatusById($bsCtx['status_id']);
        if (!$statusCtx) {
            throw new \RuntimeException("No active status rows found");
        }

        // 4. Return full context
        return $this->bookStatusRepo->hydrateBookStatusContext(
            $bookCtx,
            $statusCtx,
            $bsCtx
        );
    }

    // TODO: Adjust `Gereserveerd` mail template, it seems to be a copy and pasta of `Ligt Klaar` & `Afwezig`, and has conflicting text lines.
    // TODO: Introduce the admin its template for overdue notifications, so they know who is late for witch book, token `:loaner_name` is already included in the notification logic.
    /** API: Evaluate and perform a status change event, based on the TransitionResult */
    public function changeStatus(array $data, string $trigger = 'debug'): TransitionResult {
        $engine = new BookStatusEngine();
        // 1. Load the book context
        $bookCtx                    = App::getService('books')->findBookById($data['book_id']);
        if (!$bookCtx) {
            throw new \RuntimeException("Book not found");
        }

        // 2. Load status context for later inclusion depending on logic flow
        $statusCtx                  = App::getService('statuses')->getStatusById($data['status_type']);

        // 3. Bundle into TransitionContext, and trigger the engine to evaluate the transition
        $tx                         = new TransitionContext();
        $tx->book                   = $bookCtx;
        // 3.2 Load current book_status using the context
        $tx->bookStatus             = $this->loadBookStatusContext($bookCtx->id);
        // 3.3 Load current loan (if any)
        $tx->currentLoan            = App::getService('loan')->getCurrentLoanById($tx->bookStatus->status['id'], $bookCtx->id);

        // 3.4.1 Load active loan if a reservation flow is active
        if ($tx->book->resvLoanerId !== null && $tx->currentLoan === null) {
            $tx->currentLoan        = App::getService('loan')->getActiveLoansForBook($bookCtx->id);
        }

        // 3.4.2 If a reservation exists, load the reservation loaner context
        if ($tx->book->resvLoanerId !== null && $tx->currentLoaner === null) {
            $tx->currentLoaner = App::getService('loaner')->getLoanerById($tx->book->resvLoanerId);
        }

        // 3.5. Deal with the loaner context, and include it into the TransitionContext
        if (isset($data['loaner_name'])) {
            $selectedLoanerId       = App::getService('loaner')->findOrCreateLoaner(
                                            $data['loaner_name'],
                                            $data['loaner_email'],
                                            $data['loaner_location']);

            if ($selectedLoanerId !== null) {
                $loanerCtx          = App::getService('loaner')->getLoanerById($selectedLoanerId);
                $tx->currentLoaner  = $loanerCtx;
            }
        }

        $requiredOffice             = $this->requiredOfficeForStatus($tx, $statusCtx);

        // 3.6.1. Evaluate the automated Transport flow, and set the correct new status context
        if ($requiredOffice !== null && $tx->book->curOfficeId !== $requiredOffice) {
            $tx->newStatus          = App::getService('statuses')->getStatusById(StatusType::toId('Transport'));
            $tx->targetOfficeId     = $requiredOffice;
        } else {
            $tx->newStatus          = $statusCtx;
        }

        // 3.6.2. Reservation resolution override
        if ($tx->book->resvLoanerId !== null && $tx->book->curOfficeId === $tx->book->resvOfficeId && $statusCtx->type === StatusType::AANWEZIG) {
            // Force Ligt Klaar instead of Aanwezig
            $tx->newStatus = App::getService('statuses')->getStatusById(
                StatusType::toId('Ligt Klaar')
            );
        }

        // 3.7. (optional) Include a debug id for the admin that triggered the request
        // $tx->triggerUserId      = $_SESSION['user']['id'];

        // 4. Evaluate the collected context in the status change engine
        $result                    = $engine->transition($tx, $trigger);

        // 5. Apply the engine instructions for the status change
        $this->db->startTransaction();

        try {
            // 5.1 Ensure instructions are set before making DB changes.
            if ($result->statusChanges !== null) {
                $this->applyStatusChange($result->statusChanges, $tx->book->id);
            }

            if ($result->loanChanges !== null) {
                $this->applyLoanChanges($result->loanChanges, $tx->book);
            }

            if ($result->officeChanges !== null) {
                $this->applyOfficeChanges($result->officeChanges, $tx->book->id);
            }

            if ($result->notifications !== null) {
                $this->applyNotifications($result->notifications, $tx->bookStatus->bookStatusId, $tx->newStatus->id);
            }

            $this->db->finishTransaction();
        } catch (\Throwable $e) {
            $this->db->cancelTransaction();
            throw $e;
        }

        // 6. Build notification context, and dispatch the correct notification
        if ($result->notifications !== null) {
            $notiCtx = $this->buildNotificationContext($result);
            App::getService('notifications')->dispatch($result->notifications, $notiCtx);
        }

        // 7. Enrich the result for the controller/UI
        $result->loanerName         = $data['loaner_name'] ?? null;
        $result->loanerEmail        = $data['loaner_email'] ?? null;
        $result->loanerLocation     = $data['loaner_location'] ?? null;
        $result->bookTitle          = $bookCtx->title;
        $result->dueDate            = $result->loanChanges->endDate ?? null;

        // 7. Return the result to the controller
        return $result;
    }
}