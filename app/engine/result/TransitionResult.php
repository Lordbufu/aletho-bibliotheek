<?php
namespace App\Engine\Result;

final class TransitionResult {
    public bool $isAllowed = false;
    public ?int $newStatusId = null;
    /** @var string[] */
    public array $actions = [];   // simple action strings
    /** @var int[] */
    public array $notifications = [];
    public array $errors = [];

    public function deny(string $msg): self {
        $this->isAllowed = false;
        $this->errors[] = $msg;
        return $this;
    }
}