<?php
namespace App\Engine\Map;

final class TransitionMap {
    private array $map;

    /** */
    public function __construct(string $configPath) {
        $this->map = require $configPath . '/transitions.php';
    }

    /** */
    public function getRulesFor(string $from, string $to): array {
        return $this->map[$from][$to] ?? [];
    }
}
