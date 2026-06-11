<?php

namespace App\Services;

final class AuthService {
    private \App\Libs\UserRepo $users;

    public function __construct() {
        $this->users = new \App\Libs\UserRepo();
    }

    // Re-factor status: No changes
    /** API: Authenticate User-agent and bindings */
    public function uaIpChecker() {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';

        if (!$ua) {
            \App\App::htmlError(400);
        }

        if (isset($_SESSION['user']['ua_hash']) && $_SESSION['user']['ua_hash'] !== hash('sha256', $ua)) {
            session_unset();
        }

        if (isset($_SESSION['user']['ip_hash']) && $_SESSION['user']['ip_hash'] !== hash('sha256', $ip)) {
            session_unset();
        }
    }

    // Re-factor status: tested and working
    /** API: Authenticate user */
    public function authenticate(string $identifier, string $password, string $userAgent): ?int {
        $user = $this->users->findByIdentifier($identifier);

        if (!$user || !$user->is_active || !password_verify($password, $user->user_password)) {
            return null;
        }

        $_SESSION['user'] = [
            'id'            => $user->user_id,
            'name'          => $user->user_name,
            'permission'    => $user->permission_flags,
            'canEdit'       => in_array($user->permission_flags, ['office_admin', 'global_admin']),
            'ua_hash'       => hash('sha256', $userAgent),
            'ip_hash'       => hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '')
        ];

        return $user->user_id;
    }

    // Re-factor status: tested and working
    /** API: Check if user is logged in */
    public function isLoggedIn(): bool {
        if (empty($_SESSION['user']['id'])) {
            return false;
        }

        $uaHash = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $ipHash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');

        if ( ($_SESSION['user']['ua_hash'] ?? '') !== $uaHash || ($_SESSION['user']['ip_hash'] ?? '') !== $ipHash ) {
            return false;
        }

        $user = $this->users->findUserById($_SESSION['user']['id']);

        return $user !== null && $user->is_active;
    }

    // Re-factor status: tested and working
    /** API: Check login state, provide feedback and a forced redirect on failure */
    public function requireLogin(string $message = 'Je moet eerst inloggen.') {
        if (!$this->isLoggedIn()) {
            setFlash('global', 'failure', $message);
            \App\App::redirect('/');
        }
    }

    // Re-factor status: tested and working
    /** API: Check login state and user roles, provide feedback and a forced redirect on failure */
    public function requireRole(array $roles, string $message = 'Je hebt geen rechten om deze actie uit te voeren.'): void {
        $this->requireLogin();

        if (in_array($_SESSION['user']['permission'], $roles, true)) {
            setFlash('global', 'failure', $message);
            \App\App::redirect('/home');
        }
    }

    // Re-factor status: tested and working
    /** API: Check if user has a specific permission */
    public function hasPermission(string $permission): bool {
        $flags = $_SESSION['user']['permission'] ?? [];
        return in_array($permission, $flags, true);
    }

    // Re-factor status: tested and working
    /** API: Check if user has any of the requested permissions */
    public function hasAnyPermission(array $permissions): bool {
        $flags = $_SESSION['user']['permission'] ?? [];
        return array_intersect($permissions, $flags) !== [];
    }
}