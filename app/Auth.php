<?php

namespace App\Auth;

use App\App;

class Auth {
    protected AuthenticationService $service;

    public function __construct(array $config = []) {
        $db      = App::getService('database')->connection()->pdo();
        $logger  = App::getService('logger');
        $session = new \App\Session(); // or omit if not needed
        $this->service = new AuthenticationService($db, $logger, $session);
    }

    public function service(): AuthenticationService {
        return $this->service;
    }
}