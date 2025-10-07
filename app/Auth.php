<?php

namespace App;

use App\Auth\AuthenticationService;
use App\Validation\PasswordValidation;

/* Facade for authentication operations, provides a single access point to the AuthenticationService */
class Auth {
    protected AuthenticationService $service;
    protected PasswordValidation $validator;

    /* Construct the authentication facade, optionally accepts config for future extensibility */
    public function __construct(array $config = []) {
        $this->validator = new PasswordValidation();
        $this->service = new AuthenticationService($this->validator);
    }

    /* Attempt to log in a user */
    public function login(string $email, string $password): bool {
        return $this->service->login($email, $password);
    }

    /* Log out the current user */
    public function logout(): void {
        $this->service->logout();
    }

    /* Reset own password (office admin only) */
    public function resetOwnPassword(int $userId, string $currentPassword, string $newPassword): array {
        return $this->service->resetOwnPassword($userId, $currentPassword, $newPassword);
    }

    /* Reset another user's password (global admin only) */
    public function resetUserPassword(string $targetUserName, string $newPassword, string $confirmPassword): array {
        return $this->service->resetUserPassword($targetUserName, $newPassword, $confirmPassword);
    }

    /* Check for controllers if user can update info */
    public function canUpdateInfo(): bool {
        return $this->service->canUpdateInfo();
    }

    /**
     * Get password requirements.
     */
    // public function passwordRequirements(): array {
    //     return $this->validator->getRequirements();
    // }
}