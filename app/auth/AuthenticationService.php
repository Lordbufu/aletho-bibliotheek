<?php

namespace App\Auth;

use App\App;
use App\Validation\PasswordValidation;

/* Handles user authentication, authorization, and password management. */
class AuthenticationService {
    protected array $permissionsMap;
    protected PasswordValidation $passwordValidator;

    /** Construct the authentication service.
     *      @param PasswordValidation|null $passwordValidator Optional custom password validator.
     */
    public function __construct(PasswordValidation $passwordValidator = null) {
        $this->permissionsMap = include BASE_PATH . '/ext/config/permissions.php';
        $this->passwordValidator = $passwordValidator ?? new PasswordValidation();
    }

    /** Determine the user's role for permissions.
     *      @param array $user The user record.
     *      @return string The user's role.
     */
    private function getRole(array $user): string {
        if (!empty($user['is_global_admin'])) return 'Global Admin';
        if (!empty($user['is_office_admin'])) return 'Office Admin';
        if (!empty($user['is_loaner'])) return 'User';
        return 'Guest';
    }

    /** Check if the current user has a given permission.
     *      @param string $permission The permission to check.
     *      @return bool Whether the user has the permission.
     */
    private function can(string $permission): bool {
        $role = $_SESSION['user']['role'] ?? 'Guest';
        return in_array($permission, $this->permissionsMap[$role] ?? []);
    }

    /** Fetch a user by name.
     *      @param string $name The username.
     *      @return array|null The user record or null if not found.
     */
    private function findUserByName(string $name): ?array {
        return App::getService('database')
            ->query()
            ->fetchOne("SELECT * FROM users WHERE name = ?", [$name]);
    }

    /** Resolve the office assignment for a user.
     *      @param array $user The user record.
     *      @return int|string The office ID, 'All', or 0 if none.
     */
    private function resolveOffice(array $user) {
        if (empty($user['is_global_admin']) && empty($user['is_office_admin'])) {
            return 0;
        }

        $userOffices = App::getService('database')
            ->query()
            ->fetchAll("SELECT * FROM user_office WHERE user_id = ?", [$user['id']]);

        if (empty($userOffices)) {
            return 0;
        }

        return count($userOffices) > 1 ? 'All' : $userOffices[0]['office_id'];
    }

    /** Attempts to log in a user by there name and password, and sets there initial session data.
     *      @param string $name The username.
     *      @param string $password The plaintext password.
     *      @return bool Success status.
     */
    public function login(string $name, string $password): bool {
        $user = $this->findUserByName($name);

        if (!$user || !password_verify($password, $user['password'])) {
            App::getService('logger')->warning("Failed login attempt for {$name}", 'auth');
            return false;
        }

        $role = $this->getRole($user);
        $office = $this->resolveOffice($user);

        $_SESSION['user'] = [
            'id'      => $user['id'],
            'name'    => $user['name'],
            'role'    => $role,
            'office'  => $office,
            'canEdit' => in_array($role, ['Global Admin', 'Office Admin'], true),
        ];

        App::getService('logger')->info("User {$user['id']} logged in as {$role}", 'auth');
        return true;
    }

    /* Log out the current user, and destory its session. */
    public function logout(): void {
        session_destroy();
        $_SESSION = [];
    }

    /** Allows Office Admins to change their own password.
     *      @param int $userId The ID of the user changing their password.
     *      @param string $currentPassword The user's current password.
     *      @param string $newPassword The new password to set.
     *      @return array Status message.
     */
    public function resetOwnPassword(int $userId, string $currentPassword, string $newPassword): array {
        if (!$this->can('manage_account')) {
            App::getService('logger')->error('UNAUTHORIZED password reset attempt', 'auth');
            return ['error' => 'Unauthorized'];
        }

        if (!$this->passwordValidator->isValid($newPassword)) {
            App::getService('logger')->error('WEAK_PASSWORD', 'auth');
            return ['error' => 'Weak password'];
        }

        $storedHash = App::getService('database')
            ->query()
            ->value("SELECT password FROM users WHERE id = ?", [$userId]);

        if (!$storedHash || !password_verify($currentPassword, $storedHash)) {
            App::getService('logger')->error('INVALID_CREDENTIALS', 'auth');
            return ['error' => 'Invalid credentials'];
        }

        if ($currentPassword === $newPassword) {
            App::getService('logger')->warning('PASSWORD_UNCHANGED', 'auth');
            return ['error' => 'Password unchanged'];
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $success = App::getService('database')
            ->query()
            ->run("UPDATE users SET password = ? WHERE id = ?", [$hash, $userId]);

        if ($success) {
            App::getService('logger')->info("User {$userId} changed their own password", 'auth');
            session_regenerate_id(true);
            return ['message' => 'Your password was changed and can now be used to login.'];
        }

        App::getService('logger')->error('PASSWORD_UPDATE_FAILED', 'auth');
        return ['error' => 'Password update failed'];
    }

    /** Allows Gobal Admins to reset other user's there password.
     *      @param string $targetUserName The username of the account to reset.
     *      @param string $newPassword The new password to set.
     *      @param string $confirmPassword Confirmation of the new password.
     *      @return array Status message.
     */ 
    public function resetUserPassword(string $targetUserName, string $newPassword, string $confirmPassword): array {
        if (!$this->can('manage_account')) {
            App::getService('logger')->error('UNAUTHORIZED password reset attempt', 'auth');
            return ['error' => 'Unauthorized'];
        }

        if ($newPassword !== $confirmPassword) {
            App::getService('logger')->error('PASSWORD_MISMATCH', 'auth');
            return ['error' => 'Passwords do not match'];
        }

        if (!$this->passwordValidator->isValid($newPassword)) {
            App::getService('logger')->error('WEAK_PASSWORD', 'auth');
            return ['error' => 'Weak password'];
        }

        $user = $this->findUserByName($targetUserName);
        if (!$user) {
            App::getService('logger')->error("User {$targetUserName} not found", 'auth');
            return ['error' => 'User not found'];
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $success = App::getService('database')
            ->query()
            ->run("UPDATE users SET password = ? WHERE id = ?", [$hash, $user['id']]);

        if ($success) {
            App::getService('logger')->info("Admin reset password for: {$targetUserName}", 'auth');
            return ['message' => "Password for {$targetUserName} was changed."];
        }

        App::getService('logger')->error('PASSWORD_UPDATE_FAILED', 'auth');
        return ['error' => 'Password update failed'];
    }

    /** Check if the current user can update book info.
     *      @return bool Whether the user can update book info.
     */
    public function canUpdateInfo(): bool {
        if (!isset($_POST['_method']) || $_POST['_method'] !== 'PATCH' || $_POST['book_id'] < 1) {
            return false;
        }

        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] === 'Guest') {
            return false;
        }

        if (! $_SESSION['user']['canEdit']) {
            return false;
        }

        return true;
    }

    /**
     * Returns the current password policy requirements.
     */
    // public function passwordRequirements(): array {
    //     return $this->passwordValidator->getRequirements();
    // }
}