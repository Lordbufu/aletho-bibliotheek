<?php
namespace App\Engine\Result;

use App\Libs\Context\UserContext;

/** */
final class AuthResult {
    public bool         $success;
    public ?string      $reason = null;
    public ?UserContext $user = null;

    /** */
    public static function success(UserContext $user): self {
        $r          = new self();
        $r->success = true;
        $r->user    = $user;
        return $r;
    }

    /** */
    public static function fail(string $reason): self {
        $r          = new self();
        $r->success = false;
        $r->reason  = $reason;
        return $r;
    }
}
