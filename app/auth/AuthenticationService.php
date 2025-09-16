<?php

namespace App\Auth;

use App\App;

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
        $user = App::getService('database')->query()->fetchOne("SELECT * FROM users WHERE name = ?", [$name]);

        if ($user && password_verify($password, $user['password'])) {
            $role = $user['is_global_admin'] 
                ? 'Global Admin' 
                : ($user['is_office_admin'] ? 'Office Admin' : 'User');

            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'role' => $role,
                'canEdit' => ($role === 'Global Admin' || $role === 'Office Admin')
            ];

            App::getService('logger')->warning("User {$user['id']} logged in", 'auth');

            return true;
        }

        App::getService('logger')->warning("Failed login attempt for {$name}", 'auth');
        
        return false;
    }

    /**
     * Log out the current user.
     */
    public function logout(): void {
        session_destroy();
        $_SESSION = [];
        App::getService('logger')->warning("User logged out", 'auth');
    }

    /**
     * Check if the current user has a given permission.
     */
    public function can(string $permission): bool {
        $role = $_SESSION['user']['role'] ?? 'Guest';
        return in_array($permission, $this->permissionsMap[$role] ?? []);
    }

    /**
     * Get the current logged-in user data.
     */
    public function currentUser(): array {
        return $_SESSION['user'] ?? ['role' => 'Guest'];
    }

    /**
     * Allow a office admins to change their own password.
     * Logs minor issues as warnings, but exceptions as errors.
     */
    public function resetOwnPassword(int $userId, string $currentPassword, string $newPassword): mixed {
        if (!$this->can('manage_account')) {
            App::getService('logger')->error('UNAUTHORIZED', 'auth');
            return false;
        }

        if (!$this->passwordValidator->isValid($newPassword)) {
            App::getService('logger')->error('WEAK_PASSWORD', 'auth');
            return false;
        }

        $storedHash = App::getService('database')
            ->query()
            ->value("SELECT password FROM users WHERE id = ?", [$userId]);

        if (!$storedHash || !password_verify($currentPassword, $storedHash)) {
            App::getService('logger')->error('INVALID_CREDENTIALS', 'auth');
            return false;
        }

        if ($currentPassword === $newPassword) {
            App::getService('logger')->error('PASSWORD_UNCHANGED', 'auth');
            return false;
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $success = App::getService('database')
            ->query()
            ->fetchAll("UPDATE users SET password = ? WHERE id = ?", [$hash, $userId]);

        if ($success) {
            App::getService('logger')->warning("{$_SESSION['user']['role']} changed their own password", 'auth');
            session_regenerate_id(true);
        }

        if(!$success && empty($success)) {
            $success = ["message" => "Your password was changed, and can now be used to login !!"];
        }

        return $success;
    }

    /**
     * Allow an admin to reset another user's password.
     * Logs minor issues as warnings, but exceptions as errors.
     */
    public function resetUserPassword(string $targetUserName, string $newPassword): array {
        if (!$this->can('manage_account')) {
            App::getService('logger')->error('UNAUTHORIZED', 'auth');
            return false;
        }

        if (!$this->passwordValidator->isValid($newPassword)) {
            App::getService('logger')->error('WEAK_PASSWORD', 'auth');
            return false;
        }

        $tId = App::getService('database')
            ->query()
            ->value("SELECT id FROM users WHERE name = ?", [$targetUserName]);

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $success = App::getService('database')
            ->query()
            ->fetchAll("UPDATE users SET password = ? WHERE id = ?", [$hash, $tId]);

        if ($success) {
            App::getService('logger')->warning("{$_SESSION['user']['role']} reset password for: {$targetUserName}", 'auth');
        }

        if(!$success && empty($success)) {
            $success = ["message" => "Password for: {$targetUserName}, was changed !!"];
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
        return isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? 'Guest') !== 'Guest';
    }
}