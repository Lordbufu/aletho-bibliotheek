<?php
namespace App\Engine\Map;

use App\Engine\Result\TransitionResult;

final class TransitionMap {
    private static $map = [];

    /** API: Evaluate if transition is allowed */
    public static function canTransition(string $to, string $from): bool {
        if (!self::$map) {
            self::$map = require BASE_PATH . '/ext/config/statusTransitions.php';
        }

        return in_array($to, self::$map[$from] ?? [], true);
    }

    /** API: Shared failed transition validation code */
    public static function fail(TransitionResult $result, string $message): TransitionResult {
        $result->passed = false;
        $result->errorMessage = $message;
        return $result;
    }
}
