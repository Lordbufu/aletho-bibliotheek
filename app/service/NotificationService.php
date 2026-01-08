<?php
/** NotificationService
 *
 * This service coordinates sending event-based notifications (loan confirmations,
 * return reminders, transport requests, reservation confirmations, overdue notices)
 * to users and offices via email. It ties together:
 *   - Database lookups for user/office addresses
 *   - MailTemplateService for rendering templates with tokens
 *   - PHPMailer for actual delivery
 *
 * Usage:
 *   $notify = App::getService('notification');
 *
 *   // Notify a user about a loan confirmation
 *   $notify->notifyUser(
 *       $userId,
 *       'loan_confirm',
 *       [
 *           ':book_name' => $book['title'],
 *           ':due_date'  => $loan['due_date']
 *       ]
 *   );
 *
 *   // Notify an office about a transport request
 *   $notify->notifyOffice(
 *       $officeId,
 *       'transport_request',
 *       [
 *           ':book_name' => $book['title'],
 *           ':office'    => $office['name']
 *       ]
 *   );
 *
 * Event types supported (default templates seeded in DB):
 *   - loan_confirm              → confirmation of a new loan
 *   - loan_confirm_trans        → confirmation of a new loan and the associated transport request.
 *   - return_reminder           → reminder before due date (may include :action_block)
 *   - transport_request         → request to move a book to another office
 *   - pickup_ready              → reminder that the transport was completed, and book can be picked up.
 *   - reservation_confirmation  → confirmation of a reservation (may include :action_block)
 *   - overdue_notice            → notice when a loan is overdue
 *
 * Required tokens vary by event type:
 *   - loan_confirm: :user_name, :book_name, :due_date
 *   - loan_confirm_trans: :user_name, :book_name, :due_date, :office
 *   - return_reminder: :user_name, :book_name, :due_date, :action_block (optional)
 *   - transport_request: :user_name, :book_name, :office
 *   - pickup_ready: :user_name, :book_name, :office
 *   - reservation_confirmation: :user_name, :book_name, :reservation_token, :action_block (optional)
 *   - overdue_notice: :user_name, :book_name, :due_date, :overdue_days
 *
 * Notes:
 *   - Construct once with config; PHPMailer is initialized in the constructor and reused.
 *   - Call notifyUser() or notifyOffice() with the event type and token context.
 *   - MailTemplateService handles token replacement and conditional :action_block injection.
 *   - Errors are logged via error_log() but do not interrupt runtime.
 *   - Cron-driven batch processing (e.g. overdue reminders) can be added via processCronEvents().
 */

/** Event relations details:
 *   user notifications:
 *      loan_confirm            -> (id=2) Afwezig 
 *      pickup_ready_confirm    -> (id=4) Ligt Klaar
 *      pickup_confirm          -> (id=4 => id=2) Ligt Klaar => Afwezig
 *      return_reminder         -> (id=2) Afwezig (CRON detecting near due date)
 *      reserv_confirm          -> (id=5) Gereserveerd
 *      overdue_reminder        -> (id=2 => id=6) Afwezig => Overdatum (CRON detecting past due date)
 *
 *   admin notifications:
 *      transport_request       -> (id=3) Transport
 *      overdue_notice          -> (id=2 => id=6) Afwezig => Overdatum (CRON detecting past due date)
 */

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

/** Local TODO-List:
 *      - [ ] Evaluate if processCronEvents should be done here, or in a dedicated CronService/Controller.
 */

namespace App\Service;

use App\App;
use PHPMailer\PHPMailer\{PHPMailer, Exception};

class NotificationService {
    protected array       $config;
    protected PHPMailer   $mailer;

    public function __construct(array $config) {
        $this->config = $config;
        $this->mailer = new PHPMailer(true);
    }

    /** Helper: Internal helper to send mail via PHPMailer. */
    protected function sendMail(string $to, array $email): bool {        
        try {
            $this->mailer->isSMTP();
            $this->mailer->Host       = $this->config['host'];
            $this->mailer->Port       = $this->config['port'];
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = $this->config['username'];
            $this->mailer->Password   = $this->config['password'];

            if (!PHPMailer::validateAddress($to)) {
                // error_log("[NotificationService] Invalid recipient address: $to");
                return false;
            }

            if (!PHPMailer::validateAddress($email['from_mail'])) {
                $adress = $email['from_mail'];
                // error_log("[NotificationService] Invalid from address: $adress");
                return false;
            }

            $this->mailer->setFrom($email['from_mail'], $email['from_name']);
            $this->mailer->addAddress($to);

            $this->mailer->Subject = $email['subject'];
            $this->mailer->Body    = $email['html'];
            $this->mailer->AltBody = $email['text'];

            // Disable verbose debug in production
            $this->mailer->SMTPDebug = 0;
            // $this->mailer->Debugoutput = 'error_log'

            if (!$this->mailer->send()) {
                // error_log("[NotificationService] Mail send failed: " . $this->mailer->ErrorInfo);
                return false;
            }

            return true;
        } catch (Exception $e) {
            error_log("[NotificationService] Mail error: " . $e->getMessage());
            return false;
        }
    }

    /** Helper: Dispatch a notification event */
    protected function dispatchNotif(int $statusId, array $context): bool {
        $event = $context['event'];                                                         // Set event context for logging
        $email = App::getService('mail')->render($statusId, $context);                      // Attempt to get the full mail template

        if (!$email) {
            error_log("[NotificationService] No template found for event: $event");
            return false;
        }

        return $this->sendMail($context[':user_mail'], $email);
    }

    /** API: Dispatch and formulate status-related notification events */
    //  TODO: Review/Test AI's work here, but again looking decent so far
    public function dispatchStatusEvents(int $statusId, array $context = []): bool {
        $notiLinks  = App::getService('status')->getStatusLinks($context['book_status_id'], $statusId);
        $allOk      = true;

        if (empty($notiLinks)) {
            // error_log("[NotificationService] No notification links found for status_id={$statusId}, book_status_id={$context['book_status_id']}");
            return false;
        }

        foreach ($notiLinks as $row) {
            $event = $row['event'];
            $email = App::getService('mail')->render($row['notification_id'], $context);

            if (!$email) {
                // error_log("[NotificationService] No template rendered for event={$event}, status_id={$statusId}, notification_id={$row['notification_id']}");
                continue;
            }

            $success = $this->sendMail($context[':user_mail'], $email);

            if ($success) {
                // error_log("[NotificationService] Email sent successfully for event={$event}, to={$context[':user_mail']}");

                if (!empty($context['book_status_id'])) {
                    $ok = App::getService('status')->updateStatusLinks(
                        (int)$context['book_status_id'],
                        (int)$row['notification_id']
                    );

                    if (!$ok) {
                        // error_log("[NotificationService] Failed to update status link for book_status_id={$context['book_status_id']}, notification_id={$row['notification_id']}");
                        $allOk = false;
                    }
                }
            } else {
                // error_log("[NotificationService] Failed to send email for event={$event}, to={$context[':user_mail']}");
                $allOk = false;
            }
        }

        return $allOk;
    }

    // TODO: Evaluate if this should be done here, or in a dedicated CronService/Controller
    /** API: Process scheduled events (cron jobs). Example: send overdue notices, reminders, etc. */
    public function processCronEvents(): void {
        // PSEUDO-CODE:
        // 1) Query DB for loans that are overdue, or due soon
        // 2) For each loan, determine if a notification should be sent (e.g. overdue notice, return reminder)
        // 3) Formulate context and call dispatchStatusEvents() or dispatchNotif() accordingly
    }
}

/** Old code that has been removed, leaving it untill the re-factor is finished and working */
    // Status event map.
    // protected array $statusEventMap = [
    //     2 => ['user' => 'loan_confirm'],                                    // Afwezig
    //     5 => ['user' => 'reserv_confirm'],                                  // Gereserveerd
    //     3 => ['office' => 'transport_request'],                             // Transport
    //     4 => ['user' => 'pickup_ready_confirm'],                            // Ligt Klaar
    //     6 => ['user' => 'overdue_reminder', 'office' => 'overdue_notice'],  // Overdatum
    // ];

    // // PSEUDO-CODE: Re-factor to properly handle a event like this.
    // // Inject action block only for return_reminder and non-reserved status
    // if ($eventType === 'return_reminder' && $statusId !== 5) {
    //     $context[':action_intro'] = 'Het is ons opgevallen dat je dit boek kan verlengen, mocht je daar belang bij hebben.';
    //     $context[':action_link']  = 'https://biblioapp.nl/';
    //     $context[':action_label'] = 'Boek Verlengen';
    // }

    // /** Helper: Dispatch a notification event to the appropriate target */
    // protected function dispatchEvent(string $target, string $event, array $context): void {
    //     try {
    //         $this->sendNotif($event, $context);
    //     } catch (\Throwable $t) {
    //         error_log("[NotificationService] Notification failed: " . $t->getMessage());
    //     }
    // }

    // /*  Notify a specific office about an event. */
    // public function notifyOffice(string $event, array $context): void {
    //     $email = App::getService('mail')->render($event, $context);

    //     if (!$email) {
    //         error_log("[NotificationService] No template found for event: $event");
    //         return;
    //     }

    //     $this->sendMail($context[':user_mail'], $email);
    // }

    // foreach ($this->statusEventMap[$statusId] ?? [] as $entry) {
    //     $trigger = $entry['trigger'] ?? 'instant';

    //     // skip cron-only entries here
    //     if ($trigger === 'cron') {
    //         continue;
    //     }

    //     // if transition-based, require previousStatus match
    //     if ($trigger === 'transition') {
    //         $rel = $entry['relation'] ?? null;
    //         if (!$previousStatus || !$rel || ($rel['from'] ?? null) !== $previousStatus) {
    //             continue;
    //         }
    //     }

    //     foreach ($entry['targets'] as $target) {
    //         $context['event'] = $entry['event'];
    //         if ($target === 'user') {
    //             $mailSend = $this->dispatchNotif($statusId, $context);
    //         }

    //         if ($target === 'admin') {
    //             $baseContext['admin_email'] = "bibliotheek@aletho.nl";
    //             $mailSend = $this->dispatchNotif($statusId, $context);
    //         }
    //     }
    // }