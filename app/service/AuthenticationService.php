<?php
/** TODO List:
 *      - Review if this is even still usefull, considering `Auth` is now only the AuthenticationService.
 *      - Consider what the pro's and cons are of adding `Middleware` for authentication during requests.
 */

namespace App\Service;

use App\Auth\Authentication;
use App\Validation\PasswordValidation;

class AuthenticationService {
    protected Authentication        $auth;
    protected PasswordValidation    $validator;

    public function __construct(array $config = []) {
        $this->validator    = new PasswordValidation();
        $this->auth         = new Authentication($this->validator);
    }

    public function can(string $permission): bool {
        return $this->auth->can($permission);
    }

    public function canManageOffice(int $officeId): bool {
        return $this->auth->canManageOffice($officeId);
    }

    public function login(string $email, string $password): bool {
        return $this->auth->login($email, $password);
    }

    public function logout(): void {
        $this->auth->logout();
    }

    public function resetOwnPassword(int $userId, string $currentPassword, string $newPassword): array {
        return $this->auth->resetOwnPassword($userId, $currentPassword, $newPassword);
    }

    public function resetUserPassword(string $targetUserName, string $newPassword, string $confirmPassword): array {
        return $this->auth->resetUserPassword($targetUserName, $newPassword, $confirmPassword);
    }

    // Temp code
    public function getCurrentRole(): string {
        return $this->auth->getCurrentRole();
    }

    public function isLoggedIn(): bool {
        return $this->auth->getCurrentRole() !== 'Guest';
    }

    public function isValidRole(string $role): bool {
        return $this->auth->isValidRole($role);
    }

    public function getPermissionsForRole(string $role): array {
        return $this->auth->getPermissionsForRole($role);
    }
}