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
    protected PasswordValidator $passwordValidator;

    public function __construct(PasswordValidator $passwordValidator = null) {
        $this->permissionsMap = include BASE_PATH . '/ext/config/permissions.php';
        $this->passwordValidator = $passwordValidator ?? new PasswordValidator();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Attempt to log in a user by name and password.
     */
    public function login(string $name, string $password): bool {
        $user = App::getService('database')
            ->query()
            ->fetchOne("SELECT * FROM users WHERE name = ?", [$name]);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id'        => $user['id'],
                'type'      => $user['type']
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

        if (!$this->passwordValidator->isValid($newPassword)) {
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

        if (!$this->passwordValidator->isValid($newPassword)) {
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

    /**
     * Returns the current password policy requirements.
     *
     * This method acts as a single point of access for retrieving the
     * application's active password rules (e.g., minimum length, required
     * character types, etc.). It delegates to the underlying PasswordValidator,
     * but keeps that implementation detail hidden so calling code does not need
     * to know or depend on it directly.
     *
     * Useful for:
     *  - Displaying password guidelines in UI forms or API responses
     *  - Ensuring consistent validation rules across the application
     *  - Allowing future changes to password policy without touching callers
     *
     * @return array<string, mixed> An associative array describing the rules.
     */
    public function passwordRequirements(): array {
        return $this->passwordValidator->getRequirements();
    }

    public function check(): bool {
        return isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? 'guest') !== 'guest';
    }

    public function guest(): bool {
        return !$this->check();
    }
}