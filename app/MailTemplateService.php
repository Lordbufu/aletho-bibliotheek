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

namespace App;

class MailTemplateService {
    protected array $config;
    protected string $frameDir;

    public function __construct(array $config) {
        $this->config = $config;
        $this->frameDir = realpath(__DIR__ . '/../ext/mailFrames');
    }

    /*  Get file contents of pre-defined frame/template files */
    protected function getFrame(string $name = 'frame.html'): string {
        if ($frame === false) {
            throw new \RuntimeException("Frame template not found: $name");
        }

        return file_get_contents($this->frameDir . '/' . $name);
    }

    /*  Simple token replacement */
    protected function replaceTokens(string $content, array $tokens): string {
        if (!$content) return '';
        return str_replace(array_keys($tokens), array_values($tokens), $content);
    }

    /*  Fetch a template by event_type from DB */
    protected function getTemplate(string $eventType): ?array {
        $sql = "SELECT event_type, subject, body_html, body_text, active 
                FROM mail_templates 
                WHERE event_type = :event_type AND active = 1 
                LIMIT 1";

        return App::getService('database')->query()->fetchOne($sql, [
            'event_type' => $eventType
        ]);
    }

    /* Create action block with token confirmation, for extending book loans (HTML based). */
    protected function createActionBlock(array $tokens): string {
        try {
            if (empty($tokens[':action_link'])) {
                throw new \RuntimeException("Missing :action_link token for action block");
            }

            $fragment = $this->getFrame('action.html');
            $intro = $tokens[':action_intro'] ?? '';
            $intro = $this->replaceTokens($intro, $tokens);
            $fragment = str_replace(':action_intro', $intro, $fragment);

            $button = sprintf(
                '<a href="%s" class="action-button">Verleng</a>',
                htmlspecialchars($tokens[':action_link'], ENT_QUOTES, 'UTF-8')
            );

            return str_replace(':action_button', $button, $fragment);
        } catch (\Throwable $e) {
            error_log("[MailTemplateService] Action block error: " . $e->getMessage());
            return '<div class="action-content"><p class="action-text">Fout: Verleng link niet verzonden!</p></div>';
        }
    }

    /* Create action block with token confirmation, for extending book loans (text based). */
    protected function createActionBlockText(array $tokens): string {
        try {
            if (empty($tokens[':action_link'])) {
                throw new \RuntimeException("Missing :action_link token for plain-text action block");
            }

            $intro = $tokens[':action_intro'] ?? 'Klik op de onderstaande link om uw lening te verlengen:';

            return $intro . "\n" . $tokens[':action_link'];

        } catch (\Throwable $e) {
            error_log("[MailTemplateService] Plain-text action block error: " . $e->getMessage());
            return "Fout: Verleng link niet verzonden!";
        }
    }

    /*  Render a template with given tokens */
    public function render(string $eventType, array $tokens): ?array {
        $tpl = $this->getTemplate($eventType);
        if (!$tpl) {
            return null;
        }

        $subject   = $this->replaceTokens($tpl['subject'], $tokens);
        $bodyHtml  = $this->replaceTokens($tpl['body_html'], $tokens);
        $bodyText  = $this->replaceTokens($tpl['body_text'] ?? '', $tokens);

        // Deal with a potential action_block for extending loans.
        $actionBlockHtml = $this->createActionBlock($tokens);
        $actionBlockText = $this->createActionBlockText($tokens);

        $bodyHtml = str_replace(':action_block', $actionBlockHtml, $bodyHtml);
        $bodyText = str_replace(':action_block', $actionBlockText, $bodyText);

        $frame = $this->getFrame();
        $finalHtml = str_replace(':body', $bodyHtml, $frame);

        return [
            'subject'   => $subject,
            'html'      => $finalHtml,
            'text'      => $bodyText,
            'from_mail' => $this->config['from_email'],
            'from_name' => $this->config['from_name'],
        ];
    }
}