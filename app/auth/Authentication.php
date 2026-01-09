<?php
namespace App\Auth;

use App\App;
use App\Validation\PasswordValidation;

class Authentication {
    protected array $permissionsMap;
    protected PasswordValidation $passwordValidator;

    /** API: Construct the Authentication class with an optional password validator */
    public function __construct(PasswordValidation $passwordValidator = null) {
        $this->permissionsMap = include BASE_PATH . '/ext/config/permissionsConfig.php';
        $this->passwordValidator = $passwordValidator ?? new PasswordValidation();
    }

    /** Helper: Get the role of a user based on their database fields */
    private function getRole(array $user): string {
        if (!empty($user['is_global_admin'])) return 'Gadmin';
        if (!empty($user['is_office_admin'])) return 'Oadmin';
        if (!empty($user['is_loaner'])) return 'User';
        return 'Guest';
    }

    /** Helper: Find a user by their name */
    private function findUserByName(string $name): ?array {
        return App::getService('database')->query()->fetchOne(
            "SELECT * FROM users WHERE name = ?",
            [$name]
        );
    }

    /** Helper: Resolve the primary office for a user */
    private function resolveOffice(array $user) {
        if (empty($user['is_global_admin']) && empty($user['is_office_admin'])) {
            return null;
        }

        $userOffices = App::getService('database')->query()->fetchAll(
            "SELECT * FROM user_office WHERE user_id = ?",
            [$user['id']]
        );

        if (empty($userOffices)) {
            return null;
        }

        return count($userOffices) > 1 ? 2 : $userOffices[0]['office_id'];
    }

    /** Helper: Resolve all accessible offices for a user */
    private function resolveOffices(array $user): array {
        if (!empty($user['is_global_admin'])) {
            return [];
        }

        $rows = App::getService('database')->query()->fetchAll(
            "SELECT office_id FROM user_office WHERE user_id = ? AND active = 1",
            [$user['id']]
        );

        return array_map(fn($row) => (int)$row['office_id'], $rows);
    }

    /** API: Check if user can perform a specific action */
    public function can(string $permission): bool {
        $role = $_SESSION['user']['role'] ?? 'Guest';
        return in_array($permission, $this->permissionsMap[$role] ?? []);
    }

    /** API: Check if user can manage a specific office */
    public function canManageOffice(int $officeId): bool {
        if ($this->can('manageOffices')) {
            return true;
        }

        if ($this->can('manageOffice')) {
            $offices = $_SESSION['user']['offices'] ?? [];
            return in_array($officeId, $offices, true);
        }

        return false;
    }

    /** API: Log in a user with name and password */
    public function login(string $name, string $password): bool {
        $user = $this->findUserByName($name);

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        $role = $this->getRole($user);
        $office = $this->resolveOffice($user);

        $_SESSION['user'] = [
            'id'      => $user['id'],
            'name'    => $user['name'],
            'role'    => $role,
            'office'  => $office,
            'canEdit' => in_array($role, ['Gadmin', 'Oadmin'], true),
        ];

        return true;
    }

    /** API: Log out the current user, and destroy its session */
    public function logout(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
            $_SESSION = [];
            session_destroy();
        }
    }

    /** API: Reset own password */
    public function resetOwnPassword(int $userId, string $currentPassword, string $newPassword): array {
        if (!$this->can('pwChange')) {
            return [ 'success' => false, 'message' => 'Unauthorized' ];
        }


        $storedHash = App::getService('database')->query()->value(
            "SELECT password FROM users WHERE id = ?",
            [$userId]
        );

        if (!$storedHash || !password_verify($currentPassword, $storedHash)) {
            return [ 'success' => false, 'message' => 'Invalid credentials' ];
        }

        if ($currentPassword === $newPassword) {
            return [ 'success' => false, 'message' => 'Password unchanged' ];
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $success = App::getService('database')->query()->run(
            "UPDATE users SET password = ? WHERE id = ?",
            [$hash, $userId]
        );

        if ($success) {
            session_regenerate_id(true);

            return [ 'success' => true, 'message' => 'Your password was changed successfully.' ];
        }

        return [ 'success' => false, 'message' => 'Password update failed' ];
    }

    /** API: Reset another user's password */
    public function resetUserPassword(string $targetUserName, string $newPassword, string $confirmPassword): array {
        if (!$this->can('pwChanges')) {
            return [ 'success' => false, 'message' => 'Unauthorized' ];
        }

        $user = $this->findUserByName($targetUserName);
        if (!$user) {
            return [ 'success' => false, 'message' => 'User not found' ];
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $success = App::getService('database')->query()->run(
            "UPDATE users SET password = ? WHERE id = ?",
            [$hash, $user['id']]
        );

        if ($success) {
            return [ 'success' => true, 'message' => "Password for {$targetUserName} was changed." ];
        }

        return [ 'success' => false, 'message' => 'Password update failed' ];
    }

    /** API: Get the current user's role */
    public function getCurrentRole(): string {
        return $_SESSION['user']['role'] ?? 'Guest';
    }

    /** API: Check if a user is logged in */
    public function isLoggedIn(): bool {
        return $this->getCurrentRole() !== 'Guest';
    }

    /** API: Validate if a role exists */
    public function isValidRole(string $role): bool {
        return array_key_exists($role, $this->permissionsMap);
    }

    /** API: Get permissions for a specific role */
    public function getPermissionsForRole(string $role): array {
        return $this->permissionsMap[$role] ?? [];
    }
}