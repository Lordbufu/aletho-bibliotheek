<?php

namespace App\Services;

use App\Libs\UserRepo;
use App\Engine\Result\AuthResult;
use App\App;

final class AuthService {
    private UserRepo $users;

    public function __construct() {
        $this->users = new UserRepo();
    }

    /** API: Authenticate User-agent and bindings */
    public function uaIpChecker() {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';

        if (!$ua) {
            App::htmlError(400);
        }

        if (isset($_SESSION['user']['ua_hash']) && $_SESSION['user']['ua_hash'] !== hash('sha256', $ua)) {
            session_unset();
        }

        if (isset($_SESSION['user']['ip_hash']) && $_SESSION['user']['ip_hash'] !== hash('sha256', $ip)) {
            session_unset();
        }
    }

    /** API: Authenticate user */
    public function authenticate(string $identifier, string $password, string $userAgent): AuthResult {
        $user = $this->users->findByUsernameOrEmail($identifier);

        if (!$user || !$user->active || !password_verify($password, $user->passwordHash)) {
            return AuthResult::fail('invalid_credentials');
        }

        $_SESSION['user'] = [
            'id'        => $user->id,
            'role'      => $user->role,
            'office'    => $user->officeId,
            'canEdit'   => in_array($user->role, ['office_admin', 'global_admin']),
            'ua_hash'   => hash('sha256', $userAgent),
            'ip_hash'   => hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '')
        ];

        return AuthResult::success($user);
    }

    /** API: Check if user is logged in */
    public function isLoggedIn(): bool {
        return isset($_SESSION['user']['id']);
    }

    /** API: Check login state, provide feedback and a forced redirect on failure */
    public function requireLogin(string $message = 'Je moet eerst inloggen.') {
        if (!$this->isLoggedIn()) {
            setFlash('global', 'failure', $message);
            App::redirect('/');
        }
    }

    /** API: Check login state and user roles, provide feedback and a forced redirect on failure */
    public function requireRole(array $roles, string $message = 'Je hebt geen rechten om deze actie uit te voeren.'): void {
        $this->requireLogin();

        if (!in_array($_SESSION['user']['role'], $roles, true)) {
            setFlash('global', 'failure', $message);
            App::redirect('/home');
        }
    }

    // TODO: Review if still usefull, currently not used, but could also be functional as helper ?
    /** API: Check if user has specific role */
    // public function hasRole(string $role): bool {
    //     return isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === $role;
    // }
    
    /** API: Check if user has any of the provided roles */
    // public function hasAnyRole(array $roles): bool {
    //     return isset($_SESSION['user']['role']) && in_array($_SESSION['user']['role'], $roles, true);
    // }
}