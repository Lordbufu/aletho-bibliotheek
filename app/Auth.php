<?php

namespace App\Auth;

use App\App;

/**
 * Facade / linker for authentication operations.
 *
 * Provides a single access point to the AuthenticationService.
 */
class Auth {
    protected AuthenticationService $service;

    public function __construct(array $config = []) {
        // No need to pass DB/logger/session — AuthenticationService uses App::getService()
        $this->service = new AuthenticationService();
    }

    /**
     * Get the underlying AuthenticationService instance.
     */
    public function service(): AuthenticationService {
        return $this->service;
    }

    /**
     * Static shortcut to get the AuthenticationService from the container.
     */
    public static function get(): AuthenticationService {
        return (new static())->service();
    }
}