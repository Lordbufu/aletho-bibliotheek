<?php
namespace Ext\Controllers\Middleware;

use App\App;

class AuthMiddleware {
    protected \App\Service\AuthenticationService   $auth;

    public function __construct() {
        $this->auth = App::getService('auth');
    }

    public function normalizeRole(): void {
        $role = $this->auth->getCurrentRole();

        if (!isset($_SESSION['user']['role'])) {
            $_SESSION['user']['role'] = 'Guest';
            return;
        }

        if (!$this->auth->isValidRole($role)) {
            $_SESSION['user']['role'] = 'Guest';
        }
    }

    public function requireLogin(): void {
        $this->normalizeRole();

        if (!$this->auth->isLoggedIn()) {
            App::redirect('/login');
            exit;
        }
    }

    public function requirePermission(string $permission): void {
        $this->normalizeRole();

        if (!$this->auth->can($permission)) {
            App::redirect('/unauthorized');
            exit;
        }
    }
}