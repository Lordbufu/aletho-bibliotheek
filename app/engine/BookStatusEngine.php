<?php
namespace App\Engine;

use App\Libs\StatusTransRepo;
use App\Libs\Context\StatusTransitionContext;
use App\Engine\TransitionContext;
use App\Engine\Result\TransitionResult;

/**     context requirements:
  *         "needsBook"             : true, // check
  *         "needsLoaner"           : true, // check
  *         "needsLoan"             : true, // check
  *         "needsOffice"           : true,
  *         "needsTransport"        : true,
  *         "needsBookStatus"       : true,
  *         "needsNotificationType" : true,
  *         "needsReservation"      : true  // check
  */
/** BookStatusEngine: The goal is to evaluate status transitions, and then provide the service with the correct dataset. */
final class BookStatusEngine {
    private StatusTransRepo   $statusTrans;

    public function __construct() {
        $this->statusTrans = new StatusTransRepo();
    }

    /** Helper: Determine what operations the service has to perform */
    private function determineActions(TransitionContext $tx, StatusTransitionContext $rule): array {
        $actions = [];
        $req = $rule->context_requirements;

        // Always update status
        $actions[] = 'updateStatus';

        if (!empty($req['needsLoan'])) {
            if ($tx->loaner->loaner_id) {
                $actions[] = 'closeLoan';
            } else {
                $actions[] = 'createLoan';
            }
        }

        if (!empty($req['needsReservation'])) {
            if ($tx->reservation) {
                $actions[] = 'releaseReservation';
            } else {
                $actions[] = 'createReservation';
            }
        }

        if (!empty($req['needsDueDate'])) {
            if (!$tx->loaner->end_at) {
                $actions[] = 'setDueDate';
            }
        }

        if (!empty($req['needsTransport'])) {
            if ($tx->book->book_cur_loc !== $tx->book->book_home_loc) {
                $actions[] = 'startTransport';
            } else {
                $actions[] = 'skipTransport';
            }
        }

        if (!empty($req['needsNotificationType']) && $rule->noti_id) {
            $actions[] = 'sendNotification';
        }

        return $actions;
    }

    /** Helper: Validate requirement conditions */
    private function validateRequirements(array $req, TransitionContext $tx, TransitionResult $result): bool {
        // True preconditions
        if (!empty($req['needsBook']) && !$tx->book) {
            return $result->deny("Missing book context")->isAllowed;
        }

        if (!empty($req['needsLoaner']) && !$tx->loaner) {
            return $result->deny("Missing loaner context")->isAllowed;
        }

        // Temp removal, might still be needed later
        // if (!empty($req['needsDueDate']) && !$tx->loaner->end_at) {
        //     return $result->deny("Missing due date")->isAllowed;
        // }

        // Everything else is logic triggers, not preconditions
        return true;
    }
 
    /** API: Evaluate the requested transition */
    public function evaluate(TransitionContext $tx): TransitionResult {
        $result = new TransitionResult();

        // dd($tx);

        // Load rule
        $rule = $this->statusTrans->getTransitionByIds(
            $tx->bookStatus->status_id,
            $tx->reqStatusId
        );

        // dd($rule);

        if (!$rule) {
            return $result->deny("Transition not allowed: no rule found.");
        }

        if (!$rule->is_active) {
            return $result->deny("Transition not allowed: rule inactive.");
        }

        // Validate true preconditions
        if (!$this->validateRequirements($rule->context_requirements, $tx, $result)) {
            return $result;
        }

        // Build result
        $result->isAllowed = true;
        $result->newStatusId = $rule->to_status;

        // Add notification ID
        if ($rule->noti_id) {
            $result->notifications[] = $rule->noti_id;
        }

        // Determine actions
        $result->actions = $this->determineActions($tx, $rule);

        return $result;
    }

}
    /** API: The function that drives the book status transitions */
        // public function transition(TransitionContext $tx): TransitionResult {
        //     return match ($tx->newStatus->type) {
        //         StatusType::AFWEZIG      => $this->toAfwezig($tx),
        //         StatusType::TRANSPORT    => $this->toTransport($tx),
        //         StatusType::LIGT_KLAAR   => $this->toLigtKlaar($tx),
        //         StatusType::AANWEZIG     => $this->toAanwezig($tx),
        //         StatusType::GERESERVEERD => $this->toGereserveerd($tx),
        //         StatusType::OVERDATUM    => $this->toOverdatum($tx),
        //         default => throw new \LogicException("Unsupported target status: {$tx->newStatus->type}"),
        //     };
        // }

    /** Helper: Handle the `Afwezig` status logic */ // New transitions documented
        // private function toAfwezig(TransitionContext $tx): TransitionResult {
        //     $result                             = new TransitionResult();

        //     // 1. Validate transition
        //     $currentStatus                      = $tx->bookStatus->status['type'];
        //     if (!TransitionMap::canTransition($tx->newStatus->type, $currentStatus)) {
        //         return TransitionMap::fail($result, "Kan niet naar Afwezig vanuit {$currentStatus}.");
        //     }
            
        //     // 1. Shared: due date
        //     $days                               = $tx->newStatus->periodLength ?? 7;
        //     $dueDate                            = (new \DateTimeImmutable())->modify("+{$days} days");

        //     // 2. Shared: status instruction
        //     $statusInstr                        = new StatusChangeInstruction();
        //     $statusInstr->existingBookStatusId  = $tx->bookStatus->bookStatusId;
        //     $statusInstr->newStatusType         = StatusType::AFWEZIG;
        //     $statusInstr->active                = true;
        //     $result->statusChanges              = $statusInstr;

        //     // 3. Branch: direct loan vs transported loan
        //     $loanInstr                          = new LoanChangeInstruction();
        //     $noti                               = new NotificationInstruction();

        //     if ($currentStatus === StatusType::AANWEZIG) {
        //         // Direct loan: create new loan row
        //         $loanInstr->bookId              = $tx->book->id;
        //         $loanInstr->loanerId            = $tx->currentLoaner->id;
        //         $loanInstr->statusId            = $tx->newStatus->id;
        //         $loanInstr->startDate           = new \DateTimeImmutable();
        //         $loanInstr->endDate             = $dueDate;
        //         $loanInstr->active              = true;
        //         $noti->type                     = NotificationMap::resolveNotificationType($tx->newStatus->type, $currentStatus);
        //     } elseif ($currentStatus === StatusType::LIGT_KLAAR && $tx->currentLoan) {
        //         // Transported loan: update existing row
        //         $loanInstr->existingLoanRowId   = $tx->currentLoan->id;
        //         $loanInstr->statusId            = $tx->newStatus->id;
        //         $loanInstr->startDate           = new \DateTimeImmutable();
        //         $loanInstr->endDate             = $dueDate;
        //         $loanInstr->active              = true;
        //         $noti->type                     = NotificationMap::resolveNotificationType($tx->newStatus->type, $currentStatus);
        //     } else {
        //         $result->passed                 = false;
        //         $result->errorMessage           = "Kan niet naar Afwezig vanuit {$currentStatus}.";
        //         return $result;
        //     }

        //     // 4. Shared Notification data and setting the correct instructions
        //     $result->loanChanges                = $loanInstr;
        //     $noti->loanerId                     = $tx->currentLoaner->id;
        //     $result->notifications              = $noti;

        //     // 5. Feedback
        //     $result->userFeedbackMessage        = "Het boek is nu uitgeleend.";

        //     return $result;
        // }

    /** Helper: Handle the `Transport` status logic */ // New transitions documented
        // private function toTransport(TransitionContext $tx): TransitionResult {
        //     $result                                     = new TransitionResult();

        //     // 1. Validate transition
        //     $currentStatus                      = $tx->bookStatus->status['type'];
        //     if (!TransitionMap::canTransition($tx->newStatus->type, $currentStatus)) {
        //         return TransitionMap::fail($result, "Kan niet naar Afwezig vanuit {$currentStatus}.");
        //     }

        //     // 1. Status change → Transport
        //     $statusInstr                                = new StatusChangeInstruction();
        //     $statusInstr->existingBookStatusId          = $tx->bookStatus->bookStatusId;
        //     $statusInstr->newStatusType                 = StatusType::TRANSPORT;
        //     $statusInstr->active                        = true;

        //     if ($tx->book->resvLoanerId !== null) {
        //         $statusInstr->reservationLoanerId       = $tx->book->resvLoanerId;
        //         $statusInstr->reservationLoanerOfficeId = $tx->book->resvOfficeId;
        //     }

        //     $result->statusChanges                      = $statusInstr;

        //     // 2. Add link to Loaner so loaner linked Transport transitions can be resolved
        //     if ($tx->currentLoaner !== null) {
        //         if ($tx->book->resvLoanerId !== null) {                                                                 // End the old loan
        //             $loanInstr                          = new LoanChangeInstruction();
        //             $loanInstr->existingLoanRowId       = $tx->currentLoan->id;
        //             $loanInstr->active                  = false;
        //             $loanInstr->endDate                 = new \DateTimeImmutable();
        //         } else {
        //             $loanInstr                          = new LoanChangeInstruction();
        //             $loanInstr->bookId                  = $tx->book->id;
        //             $loanInstr->loanerId                = $tx->currentLoaner->id;
        //             $loanInstr->statusId                = StatusType::toId('Transport');
        //             $loanInstr->startDate               = new \DateTimeImmutable();

        //             if ($currentStatus === StatusType::AFWEZIG) {
        //                 $loanInstr->existingLoanRowId   = $tx->currentLoan->id;
        //                 $loanInstr->startDate           = $tx->currentLoan->startDate;
        //                 $loanInstr->endDate             = $tx->currentLoan->endDate;
        //             }

        //             $loanInstr->active                  = true;
        //         }

        //         $result->loanChanges                    = $loanInstr;
        //     }

        //     if ($result->loanChanges !== null) {
        //         // 2.1 Ensure the loanchages are null for specific reservation flows to avoid loan updates
        //         if ($result->loanChanges->statusId === null && $result->loanChanges->bookId === null) {
        //             $result->loanChanges = null;
        //         }
        //     }

        //     // 3. Office change data to ensure the flow resolve correctly later
        //     $officeInstr                                = new OfficeChangeInstruction();
        //     $officeInstr->bookId                        = $tx->book->id;
        //     $officeInstr->newOfficeId                   = $tx->targetOfficeId;
        //     $result->officeChanges                      = $officeInstr;

        //     // 4. Notification → transport_request
        //     $noti                                       = new NotificationInstruction();
        //     $noti->loanerId                             = null;
        //     $noti->type                                 = NotificationMap::resolveNotificationType($tx->newStatus->type, $currentStatus);
        //     $noti->originOfficeId                       = $tx->book->curOfficeId;
        //     $result->notifications                      = $noti;

        //     // 5. Feedback
        //     $result->userFeedbackMessage                = "Het boek wordt klaargemaakt voor transport.";

        //     return $result;
        // }

    /** Helper: Handle the 'Ligt Klaar' status logic */ // New transitions documented
        // private function toLigtKlaar(TransitionContext $tx): TransitionResult {
        //     $result                             = new TransitionResult();

        //     // 1. Validate transition
        //     $currentStatus                      = $tx->bookStatus->status['type'];
        //     if (!TransitionMap::canTransition($tx->newStatus->type, $currentStatus)) {
        //         return TransitionMap::fail($result, "Kan niet naar Afwezig vanuit {$currentStatus}.");
        //     }

        //     // 1. Status change
        //     $statusInstr                        = new StatusChangeInstruction();
        //     $statusInstr->existingBookStatusId  = $tx->bookStatus->bookStatusId;
        //     $statusInstr->newStatusType         = StatusType::LIGT_KLAAR;
        //     $statusInstr->active                = true;
        //     $result->statusChanges              = $statusInstr;

        //     // 2. Loan change (update existing Transport row, or create a new loan)
        //     $loanInstr                          = new LoanChangeInstruction();

        //     // 2.b Ensure a new loan is corretly created, if there is no currentLoan data to carry over, the lack of existingLoanRowId triggers a create, existingLoanRowId triggers a update
        //     if ($tx->book->resvLoanerId !== null) {
        //         $loanInstr->bookId              = $tx->book->id;
        //         $loanInstr->loanerId            = $tx->book->resvLoanerId;
        //         $loanInstr->recyleLoanRowId     = $tx->currentLoan->id;
        //         $loanInstr->existingLoanRowId   = null;
        //         $loanInstr->startDate           = null;
        //     } else {
        //         $loanInstr->existingLoanRowId   = $tx->currentLoan->id;
        //         $loanInstr->startDate           = $tx->currentLoan->startDate;
        //     }

        //     $loanInstr->statusId                = StatusType::toId('Ligt Klaar');
        //     $loanInstr->active                  = true;
        //     $loanInstr->endDate                 = null;
        //     $result->loanChanges                = $loanInstr;

        //     // 3. Notification
        //     $noti                               = new NotificationInstruction();
        //     $noti->loanerId                     = $tx->currentLoaner->id;
        //     $noti->type                         = NotificationMap::resolveNotificationType($tx->newStatus->type, $currentStatus);
        //     $result->notifications              = $noti;

        //     // 4. User feedback
        //     $result->userFeedbackMessage        = "The book has arrived and is ready for pickup.";

        //     return $result;
        // }

    /** Helper: handle the `Aanwezig` status logic */ // New transitions documented
        // private function toAanwezig(TransitionContext $tx): TransitionResult {
        //     $result                             = new TransitionResult();

        //     // 1. Validate transition
        //     $currentStatus                      = $tx->bookStatus->status['type'];
        //     if (!TransitionMap::canTransition($tx->newStatus->type, $currentStatus)) {
        //         return TransitionMap::fail($result, "Kan niet naar Afwezig vanuit {$currentStatus}.");
        //     }

        //     // 2. Status instruction
        //     $statusInstr                        = new StatusChangeInstruction();
        //     $statusInstr->existingBookStatusId  = $tx->bookStatus->bookStatusId;
        //     $statusInstr->newStatusType         = StatusType::AANWEZIG;
        //     $statusInstr->active                = true;
        //     $result->statusChanges              = $statusInstr;

        //     // 3. Loan close instruction (if active loan exists)
        //     if ($tx->currentLoan !== null) {
        //         $loanInstr                      = new LoanChangeInstruction();
        //         $loanInstr->existingLoanRowId   = $tx->currentLoan->id;
        //         $loanInstr->statusId            = $tx->currentLoan->statusId;
        //         $loanInstr->startDate           = $tx->currentLoan->startDate;
        //         $loanInstr->endDate             = $tx->currentLoan->endDate;
        //         $loanInstr->active              = false;
        //         $result->loanChanges            = $loanInstr;
        //     }

        //     // 4. Feedback
        //     $result->userFeedbackMessage        = "Het boek is teruggebracht.";

        //     return $result;
        // }

    /** Helper: Handle the `Gereserveerd` status logic */
        // private function toGereserveerd(TransitionContext $tx): TransitionResult {
        //     $result                                 = new TransitionResult();

        //     // 1. Validate transition
        //     $currentStatus                      = $tx->bookStatus->status['type'];
        //     if (!TransitionMap::canTransition($tx->newStatus->type, $currentStatus)) {
        //         return TransitionMap::fail($result, "Kan niet naar Afwezig vanuit {$currentStatus}.");
        //     }

        //     // 1. Status change → gereserveerd
        //     $statusInstr                            = new StatusChangeInstruction();
        //     $statusInstr->newStatusType             = StatusType::GERESERVEERD;
        //     $statusInstr->active                    = false;
        //     // 2. Add loaner meta data
        //     $statusInstr->reservationLoanerId       = $tx->currentLoaner->id;
        //     $statusInstr->reservationLoanerOfficeId = $tx->currentLoaner->officeId;
        //     // 3. Attach book data for notifications
        //     $statusInstr->existingBookStatusId      = $tx->bookStatus->bookStatusId;
        //     $result->statusChanges                  = $statusInstr;

        //     // 4. Notification → reserv_confirm
        //     $noti                                   = new NotificationInstruction();
        //     $noti->loanerId                         = $tx->currentLoaner->id;
        //     $noti->type                             = NotificationMap::resolveNotificationType($tx->newStatus->type, $currentStatus);
        //     $result->notifications                  = $noti;

        //     // 5. Feedback
        //     $result->userFeedbackMessage            = "Het boek is nu gereserveerd.";

        //     return $result;
        // }

    /** Helper: Handle the `Overdatum` status logic */ // New transitions documented
        // private function toOverdatum(TransitionContext $tx): TransitionResult {
        //     $result                              = new TransitionResult();

        //     // 1. Validate transition
        //     $currentStatus                      = $tx->bookStatus->status['type'];
        //     if (!TransitionMap::canTransition($tx->newStatus->type, $currentStatus)) {
        //         return TransitionMap::fail($result, "Kan niet naar Afwezig vanuit {$currentStatus}.");
        //     }

        //     // 2. Status change
        //     $statusInstr = new StatusChangeInstruction();
        //     $statusInstr->existingBookStatusId  = $tx->bookStatus->bookStatusId;
        //     $statusInstr->newStatusType         = StatusType::OVERDATUM;
        //     $statusInstr->active                = true;
        //     $result->statusChanges              = $statusInstr;

        //     // 3. Loan update (continue existing loan, mark overdue)
        //     if ($tx->currentLoan !== null) {
        //         $loanInstr                      = new LoanChangeInstruction();
        //         $loanInstr->existingLoanRowId   = $tx->currentLoan->id;
        //         $loanInstr->statusId            = StatusType::toId('Overdatum');
        //         $loanInstr->startDate           = $tx->currentLoan->startDate;
        //         $loanInstr->endDate             = $tx->currentLoan->endDate;
        //         $loanInstr->active              = true;
        //         $result->loanChanges            = $loanInstr;
        //     }

        //     // 4. Notification (optional)
        //     $noti                               = new NotificationInstruction();
        //     $noti->loanerId                     = $tx->currentLoan->loanerId;
        //     $noti->type                         = NotificationMap::resolveNotificationType($tx->newStatus->type, $currentStatus);
        //     $noti->originOfficeId               = $tx->book->curOfficeId;
        //     $result->notifications              = $noti;

        //     return $result;
        // }