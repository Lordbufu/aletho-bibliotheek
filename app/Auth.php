<?php
/** TODO List:
 *      - Review if this is even still usefull, considering `Auth` is now only the AuthenticationService.
 *      - Consider what the pro's and cons are of adding `Middleware` for authentication during requests.
 */

namespace App;

use App\Auth\AuthenticationService;
use App\Validation\PasswordValidation;

class Auth {
    protected AuthenticationService $service;
    protected PasswordValidation    $validator;

    public function __construct(array $config = []) {
        $this->validator    = new PasswordValidation();
        $this->service      = new AuthenticationService($this->validator);
    }

    public function can(string $permission): bool {
        return $this->service->can($permission);
    }

    public function canManageOffice(int $officeId): bool {
        return $this->service->canManageOffice($officeId);
    }

    public function login(string $email, string $password): bool {
        return $this->service->login($email, $password);
    }

    public function logout(): void {
        $this->service->logout();
    }

    public function resetOwnPassword(int $userId, string $currentPassword, string $newPassword): array {
        return $this->service->resetOwnPassword($userId, $currentPassword, $newPassword);
    }

    public function resetUserPassword(string $targetUserName, string $newPassword, string $confirmPassword): array {
        return $this->service->resetUserPassword($targetUserName, $newPassword, $confirmPassword);
    }
}