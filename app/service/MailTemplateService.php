<?php
/*  API design plan:
        $mail = App::getService('mail');
        
        $rendered = $mail->render('loan_confirm', [
            ':user_name' => $user->name,
            ':book_name' => $book->title,
            ':due_date'  => $loan->dueDate,
        ]);

        $requiredDataForEventTypes = [
            'loan_confirm'          => [':user_name', ':book_name', ':due_date'],
            'return_reminder'       => [':user_name', ':book_name', ':due_date'],
            'transport_request'     => [':user_name', ':book_name', ':office'],
            'reservation_confirmation' => [':user_name', ':book_name', ':reservation_token'],
            'overdue_notice'        => [':user_name', ':book_name', ':due_date', ':overdue_days'],
        ];
 */
/** Local TODO-List:
 *  TODO: Review required placeholder context, and double check if im actually setting everything required for non CRON related status changes.
 *  TODO: Review if Database flow for status changes, and make sure im stetting and getting the correct data, so the loaner history is also still intact.
 *  TODO: If the above Database flow isnt providing enough option for loaner history, review said flow with the client and suggest adjustments that make it better.
 */
namespace App\Service;

use App\App;

class MailTemplateService {
    protected array         $config;
    protected string        $frameDir;

    public function __construct(array $config) {
        $this->config   = $config;
        $this->frameDir = realpath(__DIR__ . '/../../ext/mailFrames');
    }

    /** Helper: Get file contents of pre-defined frame/template files */
    protected function getFrame(string $name = 'frame.html'): string {
        $path = $this->frameDir . '/' . $name;
        $frame = @file_get_contents($path);

        if ($frame === false) {
            throw new \RuntimeException("Frame template not found: $name");
        }

        return $frame;
    }

    /** Helper: Simple token replacement */
    protected function replaceTokens(string $content, array $tokens): string {
        if (!$content) return '';
        return str_replace(array_keys($tokens), array_values($tokens), $content);
    }

    /** Helper: Fetch template by status ID */
    protected function getTemplateByNotiId(int $notificationId): ?array {
        $sql = "SELECT mt.subject, mt.body_html, mt.body_text, mt.from_mail, mt.from_name
                FROM notifications n
                JOIN mail_templates mt ON n.template_id = mt.id
                WHERE n.id = :notification_id AND mt.active = 1
                LIMIT 1";

        return App::getService('database')->query()->fetchOne($sql, [
            'notification_id' => $notificationId
        ]);
    }

    /** Helper: Create optional action block with token confirmation, for extending book loans (HTML based) */
    protected function createActionBlock(array $tokens): string {
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
    protected function createActionBlockText(array $tokens): string {
        if (empty($tokens[':action_link'])) {
            return ''; // optional, nothing to render
        }

        $intro = $tokens[':action_intro'] ?? 'Klik op de onderstaande link om uw lening te verlengen:';
        return $intro . "\n" . $tokens[':action_link'];
    }

    /** API: Render a template with given tokens (context) */
    public function render(string $notificationId, array $tokens): ?array {
        $tpl = $this->getTemplateByNotiId($notificationId);
        if (!$tpl) return null;

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