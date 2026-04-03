<?php
/*  API design plan:
        $mail = App::getService('mail');
        
        $rendered = $mail->render('loan_confirm', [
            ':user_name' => $user->name,
            ':book_name' => $book->title,
            ':due_date'  => $loan->dueDate,
        ]);

        $requiredTokens = [
            'loan_confirm'          => [':user_name', ':book_name', ':due_date'],
            'transport_request'     => [':user_name', ':book_name', ':office'],
            'pickup_ready'          => [':user_name', ':book_name', ':office'],
            'pickup_confirm'        => [':user_name', ':book_name', ':due_date'],
            'reserv_confirm'        => [':user_name', ':book_name', ':due_date'],
            'return_reminder'       => [':user_name', ':book_name', ':due_date' ],
            'overdue_reminder_user' => [':user_name', ':book_name']
        ];
 */
namespace App\Services;

use App\App;
use App\Libs\MailNotificationRepo;

class MailTemplateService {
    protected MailNotificationRepo  $notification;
    protected array                 $config;
    protected string                $frameDir;

    public function __construct(array $config) {
        $this->notification = new MailNotificationRepo();
        $this->config       = $config;
        $this->frameDir     = realpath(__DIR__ . '/../../ext/mailFrames');
    }

    /** Helper: Get file contents of pre-defined frame/template files */
    private function getFrame(string $name = 'frame.html'): string {
        $path = $this->frameDir . '/' . $name;
        $frame = @file_get_contents($path);

        if ($frame === false) {
            throw new \RuntimeException("Frame template not found: $name");
        }

        return $frame;
    }

    /** Helper: Simple token replacement */
    private function replaceTokens(string $content, array $tokens): string {
        if (!$content) return '';
        return str_replace(array_keys($tokens), array_values($tokens), $content);
    }

    /** Facade\Helper: Fetch template by status ID */
    private function getTemplateByNotiId(int $notificationId): ?array {
        return $this->notification->getTemplateByNotiId($notificationId);
    }

    /** Helper: Create optional action block with token confirmation, for extending book loans (HTML based) */
    private function createActionBlock(array $tokens): string {
        if (empty($tokens[':action_link'])) {
            return ''; // optional, nothing to render
        }

        $fragment = $this->getFrame('action.html');
        $intro = $tokens[':action_intro'] ?? '';
        $intro = $this->replaceTokens($intro, $tokens);

        $fragment = str_replace(':action_intro', $intro, $fragment);

        $label = !empty($tokens[':action_label']) ? $tokens[':action_label'] : 'Verlengen';
        $button = sprintf(
            '<a href="%s" style="display:inline-block; padding:12px 24px; background-color:rgb(251, 189, 75); box-shadow: 0 2px 4px rgba(0,0,0,0.2); color:rgb(255, 255, 255); text-decoration:none; border-radius:4px; font-weight:bold; font-size:16px;">%s</a>',
            htmlspecialchars($tokens[':action_link'], ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
        );

        return str_replace(':action_button', $button, $fragment);
    }

    /** Helper: Create optional action block with token confirmation, for extending book loans (text based) */
    private function createActionBlockText(array $tokens): string {
        if (empty($tokens[':action_link'])) {
            return ''; // optional, nothing to render
        }

        $intro = $tokens[':action_intro'] ?? 'Klik op de onderstaande link om uw lening te verlengen:';
        return $intro . "\n" . $tokens[':action_link'];
    }

    /** API: Render a template with given tokens (context) */
    public function render(array $tpl, array $tokens): ?array {
        $subject   = $this->replaceTokens($tpl['subject'], $tokens);
        $bodyHtml  = $this->replaceTokens($tpl['body_html'], $tokens);
        $bodyText  = $this->replaceTokens($tpl['body_text'] ?? '', $tokens);

        // Only process action blocks if template contains :action_block
        if (strpos($tpl['body_html'], ':action_block') !== false) {
            $bodyHtml = str_replace(':action_block', $this->createActionBlock($tokens), $bodyHtml);
        }

        if (strpos($tpl['body_text'], ':action_block') !== false) {
            $bodyText = str_replace(':action_block', $this->createActionBlockText($tokens), $bodyText);
        }

        $finalHtml = str_replace(':body', $bodyHtml, $this->getFrame());

        return [
            'subject'   => $subject,
            'html'      => $finalHtml,
            'text'      => $bodyText,
            'from_mail' => $this->config['from_email'],
            'from_name' => $this->config['from_name'],
        ];
    }
}