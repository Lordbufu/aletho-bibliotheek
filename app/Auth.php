<?php

namespace App;

use App\Auth\{AuthenticationService, PasswordValidator};

/**
 * Facade for authentication operations.
 *
 * Provides a single access point to the AuthenticationService.
 */
class Auth {
    protected AuthenticationService $service;
    protected PasswordValidator $validator;

    /**
     * Construct the authentication facade.
     * Optionally accepts config for future extensibility.
     */
    public function __construct(array $config = []) {
        $this->validator = new PasswordValidator();
        $this->service = new AuthenticationService($this->validator);
    }

    /**
     * Attempt to log in a user.
     */
    public function login(string $email, string $password): bool {
        return $this->service->login($email, $password);
    }

    /**
     * Log out the current user.
     */
    public function logout(): void {
        $this->service->logout();
    }

    /**
     * Reset own password.
     */
    public function resetOwnPassword(int $userId, string $currentPassword, string $newPassword): array {
        return $this->service->resetOwnPassword($userId, $currentPassword, $newPassword);
    }

    /**
     * Reset another user's password (admin only).
     */
    public function resetUserPassword(string $targetUserName, string $newPassword, string $confirmPassword): array {
        return $this->service->resetUserPassword($targetUserName, $newPassword, $confirmPassword);
    }

    /**
     * Get password requirements.
     */
    // public function passwordRequirements(): array {
    //     return $this->validator->getRequirements();
    // }
}