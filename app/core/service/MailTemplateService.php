<?php
namespace App\Core\Services;

class MailTemplateService {
    protected string $mailDir;

    public function __construct(string $mailDir = __DIR__ . '/../../mails') {
        $this->mailDir = realpath($mailDir);
    }

    public function renderTemplate(array $segments, array $tokens): string {
        $mainTpl   = "{$this->mailDir}/example_template.html";
        $actionTpl = "{$this->mailDir}/action_template.html";

        return $this->buildEmail($mainTpl, $actionTpl, $segments, $tokens);
    }

    protected function buildEmail(string $mainTemplatePath, string $actionTemplatePath, array $segments, array $tokens): string {
        $html = file_get_contents($mainTemplatePath);
        $actionBlock = $this->createActionBlock($segments, $tokens, $actionTemplatePath);

        unset($segments['action_intro']);

        foreach ($segments as $key => $segment) {
            if ($segment === null) {
                $html = str_replace(":{$key}", '', $html);
                continue;
            }

            $rendered = str_replace(array_keys($tokens), array_values($tokens), $segment);
            $html = str_replace(":{$key}", $rendered, $html);
        }

        $html = str_replace(':action_block', $actionBlock, $html);
        return $html;
    }

    protected function createActionBlock(array $segments, array $tokens, string $actionTemplatePath): string {
        if (empty($tokens[':action_link'])) {
            return '';
        }

        $fragment = file_get_contents($actionTemplatePath);
        $rawIntro = $segments['action_intro'] ?? '';
        $intro    = str_replace(array_keys($tokens), array_values($tokens), $rawIntro);

        $fragment = str_replace(':action_intro', $intro, $fragment);

        $button = sprintf(
            '<a href="%s" class="action-button">Verleng</a>',
            htmlspecialchars($tokens[':action_link'], ENT_QUOTES, 'UTF-8')
        );

        return str_replace(':action_button', $button, $fragment);
    }
}