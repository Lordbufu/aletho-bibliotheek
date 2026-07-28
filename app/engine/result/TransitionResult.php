<?php
namespace App\Engine\Result;

final class TransitionResult {
    public bool     $isAllowed      = false;
    public ?int     $newStatusId    = null;
    /** @var Operation[] */
    public array    $operations     = [];
    /** @var int[] */
    public array    $notifications  = [];
    public array    $errors         = [];

    public function deny(string $message): self {
        $this->isAllowed = false;
        $this->errors[] = $message;
        return $this;
    }
}