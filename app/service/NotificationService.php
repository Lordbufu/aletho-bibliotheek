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
                error_log("[NotificationService] Invalid recipient address: $to");
                return false;
            }

            if (!PHPMailer::validateAddress($email['from_mail'])) {
                $adress = $email['from_mail'];
                error_log("[NotificationService] Invalid from address: $adress");
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
                error_log("[NotificationService] Mail send failed: " . $this->mailer->ErrorInfo);
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
    public function dispatchStatusEvents(int $statusId, int $previousStatus, array $context = []): bool {
        $allOk = true;

        // 1) Fetch all notification rows for this book_status
        $notiLinks = App::getService('status')->getStatusLinks($context['book_status_id'], $statusId);

        foreach ($notiLinks as $row) {
            $event = $row['event'];
            $context['event'] = $event;

            // 2) Render and send mail
            if (!empty($context['noti_id'])) {
                $email = App::getService('mail')->render($row['notification_id'], $context);
            }

            if (!$email) {
                error_log("[NotificationService] No template found for event={$event}, status_id={$statusId}");
                continue;
            }

            $success = $this->sendMail($context[':user_mail'], $email);

            // 3) Update status_noti row if mail sent
            if ($success && !empty($context['book_status_id'])) {
                $ok = App::getService('status')->updateStatusLinks((int)$context['book_status_id'], (int)$row['notification_id']);
                if (!$ok) {
                    $allOk = false;
                }
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