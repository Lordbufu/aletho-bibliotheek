<?php
namespace App;

use App\App;
use PHPMailer\PHPMailer\{PHPMailer, Exception};

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
class NotificationService {
    protected array $config;
    protected PHPMailer $mailer;

    // Status event map.
    protected array $statusEventMap = [
        2 => ['user' => 'loan_confirm'],                                    // Afwezig
        5 => ['user' => 'reserv_confirm'],                                  // Gereserveerd
        3 => ['office' => 'transport_request'],                             // Transport
        4 => ['user' => 'pickup_ready_confirm'],                            // Ligt Klaar
        6 => ['user' => 'overdue_reminder', 'office' => 'overdue_notice'],  // Overdatum
    ];

    public function __construct(array $config) {
        $this->config = $config;
        $this->mailer = new PHPMailer(true);
    }

    /*  Internal helper to send mail via PHPMailer. */
    protected function sendMail(string $to, array $email): void {        
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
                return;
            }

            if (!PHPMailer::validateAddress($email['from_mail'])) {
                $adress = $email['from_mail'];
                error_log("[NotificationService] Invalid from address: $adress");
                return;
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
            }
        } catch (Exception $e) {
            error_log("[NotificationService] Mail error: " . $e->getMessage());
        }
    }

    /* Attemp to generalize status event triggers. */
    public function dispatchStatusEvents(int $statusId, array $book, array $loaner): void {
        $context = [
            ':book_name'    => $book['title'],
            ':user_name'    => $loaner['name'],
            ':user_mail'    => $loaner['email'],
            ':user_office'  => $loaner['office_id'],
            ':due_date'     => $book['dueDate'],
            ':book_office'  => $book['office'],
            // ':action_intro' => 'Het is ons opgevallen dat je dit boek kan verlengen, mocht je daar belang bij hebben.',
            // ':action_link'  => 'https://biblioapp.nl/',
            // ':action_label' => 'Boek Verlengen'
        ];

        foreach ($this->statusEventMap[$statusId] ?? [] as $target => $event) {
            try {
                if ($target === 'user') {
                    $this->notifyUser($event, $context);
                } elseif ($target === 'office') {
                    if (!empty($context[':user_office'])) {
                        $this->notifyOffice($context[':user_office'], $event, $context);
                    }
                }
            } catch (\Throwable $t) {
                error_log("[BookStatusOrchestrator] Notification failed: " . $t->getMessage());
            }
        }
    }


    /*  Notify a specific user about an event. */
    public function notifyUser(string $event, array $context): void {
        $email = App::getService('mail')->render($event, $context);

        if (!$email) {
            error_log("[NotificationService] No template found for event: $event");
            return;
        }

        $this->sendMail($context[':user_mail'], $email);
    }

    /*  Notify a specific office about an event. */
    public function notifyOffice(int $officeId, string $event, array $context): void {
        $office = App::getService('database')->query()->fetchOne(
            "SELECT email, name FROM offices WHERE id = :id",
            ['id' => $officeId]
        );

        if (!$office) {
            error_log("[NotificationService] Office not found: $officeId");
            return;
        }

        $context[':office'] = $office['name'];

        $email = App::getService('mail')->render($event, $context);

        if (!$email) {
            error_log("[NotificationService] No template found for event: $event");
            return;
        }

        $this->sendMail($office['email'], $email);
    }

    /*  Process scheduled events (cron jobs). Example: send overdue notices, reminders, etc. */
    public function processCronEvents(): void {
        $loans = App::getService('database')->query()->fetchAll(
            "SELECT id, user_id, book_name, due_date
             FROM loans
             WHERE due_date < NOW() AND notified_overdue = 0"
        );

        foreach ($loans as $loan) {
            $context = [
                ':book_name' => $loan['book_name'],
                ':due_date'  => $loan['due_date'],
            ];

            $this->notifyUser((int)$loan['user_id'], 'overdue_notice', $context);

            App::getService('database')->query()->run(
                "UPDATE loans SET notified_overdue = 1 WHERE id = :id",
                ['id' => $loan['id']]
            );
        }
    }
}