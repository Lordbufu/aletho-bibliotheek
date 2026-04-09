<?php
namespace App\Engine;

use App\Engine\Result\TransitionResult;
use App\Engine\Instructions\{StatusChangeInstruction, LoanChangeInstruction, NotificationInstruction, OfficeChangeInstruction};
use App\Libs\Types\StatusType;

/** BookStatusEngine:
 *      The goal is to evaluate status transitions, and then provide the service with the correct dataset.
 */
final class BookStatusEngine {
    /** API: The function that drives the book status transitions */
    public function transition(TransitionContext $tx): TransitionResult {
        return match ($tx->newStatus->type) {
            StatusType::AFWEZIG      => $this->toAfwezig($tx),
            StatusType::TRANSPORT    => $this->toTransport($tx),
            StatusType::LIGT_KLAAR   => $this->toLigtKlaar($tx),
            StatusType::AANWEZIG     => $this->toAanwezig($tx),
            StatusType::GERESERVEERD => $this->toGereserveerd($tx),
            StatusType::OVERDATUM    => $this->toOverdatum($tx),
            default => throw new \LogicException("Unsupported target status: {$tx->newStatus->type}"),
        };
    }

    /** Helper: Validate Status Transition. */
    private function validateTransition(string $curStatus, array $allowedStatuses) {
        return in_array($curStatus, $allowedStatuses, true);
    }

    /** Helper: Handle the `Afwezig` status logic */
    private function toAfwezig(TransitionContext $tx): TransitionResult {
        $result                             = new TransitionResult();

        // 1. Validate transition
        $allowedStatusTypes                 = [
            StatusType::TRANSPORT,
            StatusType::LIGT_KLAAR,
            StatusType::AANWEZIG,
            StatusType::GERESERVEERD,
            StatusType::OVERDATUM
        ];
        $currentStatus                      = $tx->bookStatus->status['type'];
        
        if (!$this->validateTransition($currentStatus, $allowedStatusTypes)) {
            $result->passed                 = false;
            $result->errorMessage           = "Kan niet naar Afwezig vanuit {$currentStatus}.";
            return $result;
        }
        
        // 1. Shared: due date
        $days                               = $tx->newStatus->periodLength ?? 7;
        $dueDate                            = (new \DateTimeImmutable())->modify("+{$days} days");

        // 2. Shared: status instruction
        $statusInstr                        = new StatusChangeInstruction();
        $statusInstr->existingBookStatusId  = $tx->bookStatus->bookStatusId;
        $statusInstr->newStatusType         = StatusType::AFWEZIG;
        $statusInstr->active                = true;
        $result->statusChanges              = $statusInstr;

        // 3. Branch: direct loan vs transported loan
        $loanInstr                          = new LoanChangeInstruction();
        $notif                              = new NotificationInstruction();

        if ($currentStatus === StatusType::AANWEZIG) {
            // Direct loan: create new loan row
            $loanInstr->bookId              = $tx->book->id;
            $loanInstr->loanerId            = $tx->currentLoaner->id;
            $loanInstr->statusId            = $tx->newStatus->id;
            $loanInstr->startDate           = new \DateTimeImmutable();
            $loanInstr->endDate             = $dueDate;
            $loanInstr->active              = true;
            $notif->type                    = 'loan_confirm';                       // confirm book was handed out and the loan has started
        } elseif ($currentStatus === StatusType::LIGT_KLAAR && $tx->currentLoan) {
            // Transported loan: update existing row
            $loanInstr->existingLoanRowId   = $tx->currentLoan->id;
            $loanInstr->statusId            = $tx->newStatus->id;
            $loanInstr->startDate           = new \DateTimeImmutable();
            $loanInstr->endDate             = $dueDate;
            $loanInstr->active              = true;
            $notif->type                    = 'pickup_confirm';                     // confirm book was picked up and loan has started
        } else {
            $result->passed                 = false;
            $result->errorMessage           = "Kan niet naar Afwezig vanuit {$currentStatus}.";
            return $result;
        }

        // 4. Shared Notification data and setting the correct instructions
        $result->loanChanges                = $loanInstr;
        $notif->loanerId                    = $tx->currentLoaner->id;
        $result->notifications              = $notif;

        // 5. Feedback
        $result->userFeedbackMessage        = "Het boek is nu uitgeleend.";

        return $result;
    }

    /** Helper: Handle the `Transport` status logic */
    private function toTransport(TransitionContext $tx): TransitionResult {
        $result                                     = new TransitionResult();

        // 1. Validate transition
        $allowedStatusTypes                 = [
            StatusType::AFWEZIG,
            StatusType::LIGT_KLAAR,
            StatusType::AANWEZIG,
            StatusType::GERESERVEERD,
            StatusType::OVERDATUM
        ];
        $currentStatus                              = $tx->bookStatus->status['type'];

        if (!$this->validateTransition($currentStatus, $allowedStatusTypes)) {
            $result->passed                 = false;
            $result->errorMessage           = "Kan niet naar Gereserveerd vanuit {$currentStatus}.";
            return $result;
        }

        // 1. Status change → Transport
        $statusInstr                                = new StatusChangeInstruction();
        $statusInstr->existingBookStatusId          = $tx->bookStatus->bookStatusId;
        $statusInstr->newStatusType                 = StatusType::TRANSPORT;
        $statusInstr->active                        = true;

        if ($tx->book->resvLoanerId !== null) {
            $statusInstr->reservationLoanerId       = $tx->book->resvLoanerId;
            $statusInstr->reservationLoanerOfficeId = $tx->book->resvOfficeId;
        }

        $result->statusChanges                      = $statusInstr;

        // 2. Add link to Loaner so loaner linked Transport transitions can be resolved
        if ($tx->currentLoaner !== null) {
            if ($tx->book->resvLoanerId !== null) {                                                                 // End the old loan
                $loanInstr                          = new LoanChangeInstruction();
                $loanInstr->existingLoanRowId       = $tx->currentLoan->id;
                $loanInstr->active                  = false;
                $loanInstr->endDate                 = new \DateTimeImmutable();
            } else {
                $loanInstr                          = new LoanChangeInstruction();
                $loanInstr->bookId                  = $tx->book->id;
                $loanInstr->loanerId                = $tx->currentLoaner->id;
                $loanInstr->statusId                = StatusType::toId('Transport');
                $loanInstr->startDate               = new \DateTimeImmutable();

                if ($currentStatus === StatusType::AFWEZIG) {
                    $loanInstr->existingLoanRowId   = $tx->currentLoan->id;
                    $loanInstr->startDate           = $tx->currentLoan->startDate;
                    $loanInstr->endDate             = $tx->currentLoan->endDate;
                }

                $loanInstr->active                  = true;
            }

            $result->loanChanges                    = $loanInstr;
        }

        // 2.1 Ensure the loanchages are null for specific reservation flows to avoid loan updates
        if ($result->loanChanges->statusId === null && $result->loanChanges->bookId === null) {
            $result->loanChanges = null;
        }

        // 3. Office change data to ensure the flow resolve correctly later
        $officeInstr                                = new OfficeChangeInstruction();
        $officeInstr->bookId                        = $tx->book->id;
        $officeInstr->newOfficeId                   = $tx->targetOfficeId;
        $result->officeChanges                      = $officeInstr;

        // 4. Notification → transport_request
        $noti                                       = new NotificationInstruction();
        $noti->loanerId                             = null;
        $noti->type                                 = 'transport_request';
        $noti->originOfficeId                       = $tx->book->curOfficeId;
        $result->notifications                      = $noti;

        // 5. Feedback
        $result->userFeedbackMessage                = "Het boek wordt klaargemaakt voor transport.";

        return $result;
    }

    /** Helper: Handle the 'Ligt Klaar' status logic */
    private function toLigtKlaar(TransitionContext $tx): TransitionResult {
        $result                             = new TransitionResult();

        // 1. Validate transition
        $allowedStatusTypes                 = [
            StatusType::AFWEZIG,
            StatusType::TRANSPORT,
            StatusType::GERESERVEERD,
            StatusType::OVERDATUM
        ];
        $currentStatus                      = $tx->bookStatus->status['type'];

        if (!$this->validateTransition($currentStatus, $allowedStatusTypes)) {
            $result->passed                 = false;
            $result->errorMessage           = "Kan niet naar Ligt Klaar vanuit {$currentStatus}.";
            return $result;
        }

        // 1. Status change
        $statusInstr                        = new StatusChangeInstruction();
        $statusInstr->existingBookStatusId  = $tx->bookStatus->bookStatusId;
        $statusInstr->newStatusType         = StatusType::LIGT_KLAAR;
        $statusInstr->active                = true;
        $result->statusChanges              = $statusInstr;

        // 2. Loan change (update existing Transport row, or create a new loan)
        $loanInstr                          = new LoanChangeInstruction();

        // 2.b Ensure a new loan is corretly created, if there is no currentLoan data to carry over
            // the lack of existingLoanRowId triggers a create
            // existingLoanRowId triggers a update
        if ($tx->book->resvLoanerId !== null) {
            $loanInstr->bookId              = $tx->book->id;
            $loanInstr->loanerId            = $tx->book->resvLoanerId;
            $loanInstr->recyleLoanRowId     = $tx->currentLoan->id;
            $loanInstr->existingLoanRowId   = null;
            $loanInstr->startDate           = null;
        } else {
            $loanInstr->existingLoanRowId   = $tx->currentLoan->id;
            $loanInstr->startDate           = $tx->currentLoan->startDate;
        }

        $loanInstr->statusId                = StatusType::toId('Ligt Klaar');
        $loanInstr->active                  = true;
        $loanInstr->endDate                 = null;
        $result->loanChanges                = $loanInstr;

        // 3. Notification
        $noti                               = new NotificationInstruction();
        $noti->loanerId                     = $tx->currentLoaner->id;
        $noti->type                         = 'pickup_ready';
        $result->notifications              = $noti;

        // 4. User feedback
        $result->userFeedbackMessage        = "The book has arrived and is ready for pickup.";

        return $result;
    }

    /** Helper: handle the `Aanwezig` status logic */
    private function toAanwezig(TransitionContext $tx): TransitionResult {
        $result                             = new TransitionResult();

        // 1. Validate transition
        $allowedStatusTypes                 = [
            StatusType::AFWEZIG,
            StatusType::TRANSPORT,
            StatusType::OVERDATUM
        ];
        $currentStatus                      = $tx->bookStatus->status['type'];

        if (!$this->validateTransition($currentStatus, $allowedStatusTypes)) {
            $result->passed                 = false;
            $result->errorMessage           = "Kan niet naar Aanwezig vanuit {$currentStatus}.";
            return $result;
        }

        // 2. Status instruction
        $statusInstr                        = new StatusChangeInstruction();
        $statusInstr->existingBookStatusId  = $tx->bookStatus->bookStatusId;
        $statusInstr->newStatusType         = StatusType::AANWEZIG;
        $statusInstr->active                = true;
        $result->statusChanges              = $statusInstr;

        // 3. Loan close instruction (if active loan exists)
        if ($tx->currentLoan !== null) {
            $loanInstr                      = new LoanChangeInstruction();
            $loanInstr->existingLoanRowId   = $tx->currentLoan->id;
            $loanInstr->statusId            = $tx->currentLoan->statusId;
            $loanInstr->startDate           = $tx->currentLoan->startDate;
            $loanInstr->endDate             = $tx->currentLoan->endDate;
            $loanInstr->active              = false;
            $result->loanChanges            = $loanInstr;
        }

        // 4. Feedback
        $result->userFeedbackMessage        = "Het boek is teruggebracht.";

        return $result;
    }

    /** Helper: Handle the `Gereserveerd` status logic */
    private function toGereserveerd(TransitionContext $tx): TransitionResult {
        $result                                 = new TransitionResult();

        // 1. Validate transition
        $allowedStatusTypes                 = [
            StatusType::AFWEZIG,
            StatusType::LIGT_KLAAR,
            StatusType::OVERDATUM
        ];
        $currentStatus                          = $tx->bookStatus->status['type'];

        if (!$this->validateTransition($currentStatus, $allowedStatusTypes)) {
            $result->passed                 = false;
            $result->errorMessage           = "Kan niet naar Gereserveerd vanuit {$currentStatus}.";
            return $result;
        }

        // 1. Status change → gereserveerd
        $statusInstr                            = new StatusChangeInstruction();
        $statusInstr->newStatusType             = StatusType::GERESERVEERD;
        $statusInstr->active                    = false;
        // 2. Add loaner meta data
        $statusInstr->reservationLoanerId       = $tx->currentLoaner->id;
        $statusInstr->reservationLoanerOfficeId = $tx->currentLoaner->officeId;
        // 3. Attach book data for notifications
        $statusInstr->existingBookStatusId      = $tx->bookStatus->bookStatusId;
        $result->statusChanges                  = $statusInstr;

        // 4. Notification → reserv_confirm
        $noti                                   = new NotificationInstruction();
        $noti->loanerId                         = $tx->currentLoaner->id;
        $noti->type                             = 'reserv_confirm';
        $result->notifications                  = $noti;

        // 5. Feedback
        $result->userFeedbackMessage            = "Het boek is nu gereserveerd.";

        return $result;
    }

    /** Helper: Handle the `Overdatum` status logic */
    private function toOverdatum(TransitionContext $tx): TransitionResult {
        $result                              = new TransitionResult();
        // 1. Validate transition
        $allowedStatusTypes                  = [
            StatusType::AFWEZIG
        ];
        $currentStatus                       = $tx->bookStatus->status['type'];

        if (!$this->validateTransition($currentStatus, $allowedStatusTypes)) {
            $result->passed                  = false;
            $result->errorMessage            = "Kan niet naar Overdatum vanuit {$currentStatus}.";
            return $result;
        }

        // 2. Status change
        $statusInstr = new StatusChangeInstruction();
        $statusInstr->existingBookStatusId  = $tx->bookStatus->bookStatusId;
        $statusInstr->newStatusType         = StatusType::OVERDATUM;
        $statusInstr->active                = true;
        $result->statusChanges              = $statusInstr;

        // 3. Loan update (continue existing loan, mark overdue)
        if ($tx->currentLoan !== null) {
            $loanInstr                      = new LoanChangeInstruction();
            $loanInstr->existingLoanRowId   = $tx->currentLoan->id;
            $loanInstr->statusId            = StatusType::toId('Overdatum');
            $loanInstr->startDate           = $tx->currentLoan->startDate;
            $loanInstr->endDate             = $tx->currentLoan->endDate;
            $loanInstr->active              = true;
            $result->loanChanges            = $loanInstr;
        }

        // 4. Notification (optional)
        $noti                               = new NotificationInstruction();
        $noti->loanerId                     = $tx->currentLoan->loanerId;
        $noti->type                         = 'overdue_reminder_user';
        $noti->originOfficeId               = $tx->book->curOfficeId;
        $result->notifications              = $noti;

        return $result;
    }
}