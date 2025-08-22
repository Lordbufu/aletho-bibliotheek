<?php

namespace App\Auth;

use App\App;
use PDO;
use Exception;

/**
 * Handles user authentication, authorization, and password management.
 */
class AuthenticationService {
    protected $permissionsMap;

    public function __construct() {
        $this->permissionsMap = include BASE_PATH . '/ext/config/permissions.php';

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }


    /**
     * Attempt to log in a user by email and password.
     */
    public function login(string $email, string $password): bool {
        $stmt = App::getService('database')->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id'        => $user['id'],
                'role'      => $user['role'],
                'office_id' => $user['office_id']
            ];

            App::getService('logger')->warning("User {$user['id']} logged in", 'auth');
            return true;
        }

        App::getService('logger')->warning("Failed login attempt for {$email}", 'auth');
        return false;
    }

    /**
     * Log out the current user.
     */
    public function logout(): void {
        session_destroy();
        App::getService('logger')->warning("User logged out", 'auth');
    }

    /**
     * Check if the current user has a given permission.
     */
    public function can(string $permission): bool {
        $role = $_SESSION['user']['role'] ?? 'guest';
        return in_array($permission, $this->permissionsMap[$role] ?? []);
    }

    /**
     * Get the current logged-in user data.
     */
    public function currentUser(): array {
        return $_SESSION['user'] ?? ['role' => 'guest'];
    }

    /**
     * Allow a user to change their own password.
     */
    public function resetOwnPassword(int $userId, string $currentPassword, string $newPassword): bool {
        if (!$this->can('manage_account')) {
            throw new Exception('UNAUTHORIZED');
        }

        if (!PasswordValidator::isValid($newPassword)) {
            throw new Exception('WEAK_PASSWORD');
        }

        $stmt = App::getService('database')->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $storedHash = $stmt->fetchColumn();

        if (!$storedHash || !password_verify($currentPassword, $storedHash)) {
            throw new Exception('INVALID_CREDENTIALS');
        }

        if ($currentPassword === $newPassword) {
            throw new Exception('PASSWORD_UNCHANGED');
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $update = App::getService('database')->prepare("UPDATE users SET password = ? WHERE id = ?");
        $success = $update->execute([$hash, $userId]);

        if ($success) {
            App::getService('logger')->warning("User {$userId} changed their password", 'auth');
            session_regenerate_id(true);
        }

        return $success;
    }

    /**
     * Allow an admin to reset another user's password.
     */
    public function resetUserPassword(int $targetUserId, string $newPassword): bool {
        if (!$this->can('manage_accounts')) {
            throw new Exception('UNAUTHORIZED');
        }

        if (!PasswordValidator::isValid($newPassword)) {
            throw new Exception('WEAK_PASSWORD');
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $update = App::getService('database')->prepare("UPDATE users SET password = ? WHERE id = ?");
        $success = $update->execute([$hash, $targetUserId]);

        if ($success) {
            $adminId = $_SESSION['user']['id'] ?? 'unknown';
            App::getService('logger')->warning("Admin {$adminId} reset password for user {$targetUserId}", 'auth');
        }

        return $success;
    }
}