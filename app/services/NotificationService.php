<?php
namespace App\Services;

use App\App;
use PHPMailer\PHPMailer\{PHPMailer, Exception};
use App\Libs\MailNotificationRepo;
use App\Engine\Instructions\NotificationInstruction;
use App\Libs\Context\NotificationContext;

class NotificationService {
    private MailNotificationRepo  $mailNotiRepo;
    private array                 $config;
    private PHPMailer             $mailer;

    public function __construct(array $config) {
        $this->config       = $config;
        $this->mailer       = new PHPMailer(true);
        $this->mailNotiRepo = new MailNotificationRepo();
    }

    /** Helper: Internal helper to send mail via PHPMailer. */
    private function sendMail(string $to, array $email): bool {        
        try {
            // Clear any lingering mail data for our mail send loops.
            $this->mailer->clearAddresses();
            $this->mailer->clearCCs();
            $this->mailer->clearBCCs();

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

            error_log("[NotificationService] Send mail to this adress: {$to}");
            $this->mailer->setFrom($email['from_mail'], $email['from_name']);
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $email['subject'];
            $this->mailer->Body    = $email['html'];
            $this->mailer->AltBody = $email['text'];

            // Disable verbose debug in production
            $this->mailer->SMTPDebug = 0;
            // $this->mailer->Debugoutput = 'error_log'

            // Quick win: send response to browser immediately
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }

            // Background mail send
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

    /** Helper: Build token array to fill template placeholders */
    private function buildTokens(string $type, NotificationContext $tx): array {
        $userName = $tx->loanerName ?? '';

        if ($type === 'transport_request' && $tx->adminName) {
            $userName = $tx->adminName ?? '';
        }

        return [
            ':user_name'    => $userName,
            ':book_name'    => $tx->bookName ?? '',
            ':due_date'     => $tx->dueDate ?? '',
            ':office'       => $tx->officeName ?? '',
            ':action_block' => '', // optional for now
        ];
    }

    /** API: Dispatch and formulate status-related notification events */
    public function dispatch(NotificationInstruction $instr, NotificationContext $tx): bool {
        $loanerNotiData = [];
        $adminNotiData  = [];

        // 1. Fetch notification + template
        $noti = $this->mailNotiRepo->getNotificationByType($instr->type);
        if (!$noti) {
            error_log("[NotificationService] Unknown notification type: {$instr->type}");
            return false;
        }

        $template = $this->mailNotiRepo->getTemplateByNotiId($noti['template_id']);
        if (!$template) {
            error_log("[NotificationService] Unknown mail template id: {$noti['template_id']}");
            return false;
        }

        // 2. Build tokens
        $tokens = $this->buildTokens($instr->type, $tx);

        // 3.1. Build Loaner mail data
        if ($instr->type !== 'transport_request' && $tx->loanerEmail) {
            $loanerTokens = $tokens;
            $loanerTokens[':user_name'] = $tx->loanerName ?? '';
            $loanerNotiData = [
                'mail'      => App::getService('mail')->render($template, $loanerTokens),
                'recipient' => $tx?->loanerEmail
            ];

            if (!$loanerNotiData['recipient']) {
                error_log("[NotificationService] Failed fetch recipient for: {$tx->loanerEmail}");
            }

            if (!$loanerNotiData['mail']) {
                error_log("[NotificationService] Failed to render template for type: {$instr->type}");
            }
        }

        // 3.2. Build Admin mail data
        if ($tx->adminEmail) {
            $adminTokens = $tokens;
            $adminTokens[':user_name'] = $tx->adminName ?? '';

            // 3.2.1 Include loaner name if relevant for overdue notifications
            if ($loanerNotiData) {
                $adminTokens[':loaner_name'] = $tx->loanerName ?? '';
            }

            $adminNotiData = [
                'mail'      => App::getService('mail')->render($template, $adminTokens),
                'recipient' => $tx?->adminEmail
            ];

            if (!$adminNotiData['recipient']) {
                error_log("[NotificationService] Failed fetch recipient for: {$tx->adminEmail}");
            }

            if (!$adminNotiData['mail']) {
                error_log("[NotificationService] Failed to render template for type: {$instr->type}");
            }
        }

        // 4. Attempt to send mail to the correct people
        $successLoaner = false;
        $successAdmin  = false;

        if ($loanerNotiData) {
            $successLoaner = $this->sendMail($loanerNotiData['recipient'], $loanerNotiData['mail']);
            if (!$successLoaner) {
                error_log("[NotificationService] Failed to send mail to: {$loanerNotiData['recipient']}");
            }
        }

        if ($adminNotiData) {
            $successAdmin = $this->sendMail($adminNotiData['recipient'], $adminNotiData['mail']);
            if (!$successAdmin) {
                error_log("[NotificationService] Failed to send mail to: {$adminNotiData['recipient']}");
            }
        }

        $success = $successLoaner || $successAdmin;
        
        // 5. Update status_noti (optional)
        if ($success) {
            $this->mailNotiRepo->markNotificationSent($tx->bookStatusId, $noti['id']);
        }

        return $success;
    }
}