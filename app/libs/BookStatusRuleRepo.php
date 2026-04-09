<?php
namespace App\Libs;

/** Internal DTO: Context for evaluating rules */
class BookStatusRuleContext {
    public array    $book;
    public int      $oldStatus;
    public int      $requestedStatus;
    public ?array   $loaner;
    public array    $requestStatus;

    public function __construct(array $book, int $oldStatus, int $requestedStatus, ?array $loaner, array $requestStatus) {
        $this->book             = $book;
        $this->oldStatus        = $oldStatus;
        $this->requestedStatus  = $requestedStatus;
        $this->loaner           = empty($loaner) ? null : $loaner;
        $this->requestStatus    = $requestStatus;
    }
}

/** Internal DTO: Decision returned by rule evaluation */
class BookStatusRuleDecision {
    public ?int $overrideStatus     = null;
    public ?int $targetOffice       = null;
    public bool $shouldUpdateOffice = false;
    public bool $transport          = false;
    public ?string $trigger         = null;
}

/** Main rule engine */
class BookStatusRuleRepo {
    /** Public API: Evaluate all rules and return a decision */
    public function evaluate(array $book, int $oldStatus, int $requestedStatus, ?array $loaner, array $requestStatus): array {
        $ctx        = new BookStatusRuleContext($book, $oldStatus, $requestedStatus, $loaner, $requestStatus);
        $decision   = new BookStatusRuleDecision();

        $this->applyLoanerRules($ctx, $decision);
        $this->applyReturnFlowRules($ctx, $decision);
        $this->applyTransportRules($ctx, $decision);
        $this->applyTargetOfficeRules($ctx, $decision);
        $this->applyOfficeUpdateRules($ctx, $decision);

        if ($this->autoTrigger($ctx)) {                                                         // Evaluate the trigger seperatly
            $decision->trigger = 'auto_action';
        }

        return [
            'overrideStatus'        => $decision->overrideStatus,
            'targetOffice'          => $decision->targetOffice,
            'transport'             => $decision->transport,
            'trigger'               => $decision->trigger,
            'shouldUpdateOffice'    => $decision->shouldUpdateOffice,
        ];
    }

    /** Helper: Evaluate the trigger state */
    protected function autoTrigger(BookStatusRuleContext $ctx): bool {
        if ($ctx->requestedStatus !== $ctx->oldStatus) {                                        // If the user explicitly requested a different status → user_action
            return false;
        }

        return true;                                                                            // If the rule engine is forcing a transition → auto_action
    }

    /** Rule group: Loaner-based logic. */
    protected function applyLoanerRules(BookStatusRuleContext $ctx, BookStatusRuleDecision $d): void {
        if ($ctx->loaner === null) {
            return;
        }

        $loanerOffice = $ctx->loaner['office'] ?? null;                                         // If loaner office differs from current office → transport
        
        if ($loanerOffice !== null && $loanerOffice !== $ctx->book['cur_office']) {
            $d->transport       = true;
            $d->targetOffice    = $loanerOffice;
        }
    }

    /** Rule group: Return-flow logic - Afwezig (2) → Aanwezig (1) at wrong office triggers Transport. */
    protected function applyReturnFlowRules(BookStatusRuleContext $ctx, BookStatusRuleDecision $d): void {
        if ($ctx->requestedStatus === 1 && $ctx->oldStatus === 2 && $ctx->book['cur_office'] !== $ctx->book['home_office']) {
            $d->transport       = true;
            $d->overrideStatus  = 3;                                                            // Transport
            $d->targetOffice    = $ctx->book['home_office'];
        }
    }

    /** Rule group: Transport-based logic. */
    protected function applyTransportRules(BookStatusRuleContext $ctx, BookStatusRuleDecision $d): void {
        $statusType   = $ctx->requestStatus['type'] ?? null;
        $loanerOffice = $ctx->loaner['office'] ?? null;

        if ($statusType !== 'Afwezig') {                                                        // Only Afwezig can imply transport here
            return;
        }

        $targetOffice = $loanerOffice ?: (int)$ctx->book['home_office'];                        // Decide targetOffice for Afwezig

        if ($ctx->book['cur_office'] !== $targetOffice) {                                       // Only mark transport if there's actually movement
            $d->transport    = true;
            $d->targetOffice = $targetOffice;
        }
    }

    /** Rule group: Target office-based logic. */
    protected function applyTargetOfficeRules(BookStatusRuleContext $ctx, BookStatusRuleDecision $d): void {
        if ($d->targetOffice !== null) {                                                        // If a previous rule already set a target office, don't override it
            // error_log("TargetOfficeRules: skipped, already set to {$d->targetOffice}");
            return;
        }

        $statusType   = $ctx->requestStatus['type'] ?? null;

        if ($ctx->requestedStatus === 1 && $ctx->oldStatus === 3 && $ctx->loaner === null) {    // Case 1: Aanwezig after Transport, no loaner → send to home office
            // error_log("TargetOfficeRules: Transport → Aanwezig, setting to home_office=" . $ctx->book['home_office']);
            $d->targetOffice    = (int)$ctx->book['home_office']; return;
        }

        if ($statusType === 'Afwezig') {                                                        // Case 2: Afwezig logic (loaner / home)
            $loanerOffice       = $ctx->loaner['office'] ?? null;
            $d->targetOffice    = $loanerOffice ?: (int)$ctx->book['home_office'];              // Afwezig → target = loaner office or home office
            // error_log("TargetOfficeRules: Afwezig, targetOffice=" . $d->targetOffice);
            return;
        }

        $d->targetOffice        = (int)$ctx->book['cur_office'];                                // Default: target office = current office
        // error_log("TargetOfficeRules: default, targetOffice=" . $d->targetOffice);
    }

    /** Rule group: Update office-based logic. */
    protected function applyOfficeUpdateRules(BookStatusRuleContext $ctx, BookStatusRuleDecision $d): void {
        $finalStatus = $d->overrideStatus ?? $ctx->requestedStatus;                             // Determine final status (override or requested)

        // error_log("OfficeRule: old={$ctx->oldStatus}, req={$ctx->requestedStatus}, final={$finalStatus}, cur={$ctx->book['cur_office']}, target={$d->targetOffice}");

        if ($finalStatus === 4) {                                                               // Case 1: Ligt Klaar (pickup flow)
            if ($d->targetOffice !== null && $ctx->book['cur_office'] !== $d->targetOffice) {
                $d->shouldUpdateOffice = true;
            }
            return;
        }

        if ($finalStatus === 1 && $ctx->oldStatus === 3) {                                      // Case 2: Aanwezig after Transport (return-to-home flow)
            if ($d->targetOffice !== null && $ctx->book['cur_office'] !== $d->targetOffice) {
                $d->shouldUpdateOffice = true;
            }
            return;
        }
    }
}