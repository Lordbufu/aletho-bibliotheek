<?php
namespace App\Auth;

use App\App;
use App\Validation\PasswordValidation;

class AuthenticationService {
    protected array $permissionsMap;
    protected PasswordValidation $passwordValidator;

    public function __construct(PasswordValidation $passwordValidator = null) {
        $this->permissionsMap = include BASE_PATH . '/ext/config/permissions.php';
        $this->passwordValidator = $passwordValidator ?? new PasswordValidation();
    }

    /*  Map database field to a easier to user string value. */
    private function getRole(array $user): string {
        if (!empty($user['is_global_admin'])) return 'Gadmin';
        if (!empty($user['is_office_admin'])) return 'Oadmin';
        if (!empty($user['is_loaner'])) return 'User';
        return 'Guest';
    }

    /*  Get user based on the name field. */
    private function findUserByName(string $name): ?array {
        return App::getService('database')->query()->fetchOne(
            "SELECT * FROM users WHERE name = ?",
            [$name]
        );
    }

    /*  Get office id associated with the user. */
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

    /*  Get all office IDs associated with the user, returns an array of office IDs (empty if none). */
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

    /*  Public function to check if current session user has  a permission. */
    public function can(string $permission): bool {
        $role = $_SESSION['user']['role'] ?? 'Guest';
        return in_array($permission, $this->permissionsMap[$role] ?? []);
    }

    /*  Check if user can edit office based on permission and/or office id. */
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

    /*  Attempts to log in a user by there name and password, and sets there initial session data. */
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
            'canEdit' => in_array($role, ['Global Admin', 'Office Admin'], true),
        ];

        return true;
    }

    /*  Log out the current user, and destory its session. */
    public function logout(): void {
        session_destroy();
        $_SESSION = [];
        session_regenerate_id(true);
    }

    /*  Password reset, for Office Admins. */
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

    /*  Password reset for Global Admins, allowing it for any account. */
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
}