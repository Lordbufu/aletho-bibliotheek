<?php

namespace App;

use App\Auth\{AuthenticationService, PasswordValidator};

/**
 * Facade / linker for authentication operations.
 *
 * Provides a single access point to the AuthenticationService.
 */
class Auth {
    protected AuthenticationService $service;
    protected PasswordValidator $validator;

    public function __construct(array $config = []) {
        $this->validator = new PasswordValidator();
        $this->service = new AuthenticationService($this->validator);
    }

    public function login(string $email, string $password): bool {
        return $this->service->login($email, $password);
    }

    public function check(): bool {
        return $this->service->check();
    }

    public function guest(): bool {
        return $this->service->guest();
    }

    public function user(): ?array {
        return $this->service->currentUser();
    }
    
    public function logout() {
        return $this->service->logout();
    }

    public function can(string $permission): bool {
        return $this->service->can($permission);
    }

    public function resetOwnPassword(int $userId, string $currentPassword, string $newPassword): bool {
        return $this->service->resetOwnPassword($userId, $currentPassword, $newPassword);
    }

    public function resetUserPassword(int $targetUserId, string $newPassword): bool {
        return $this->service->resetUserPassword($targetUserId, $newPassword);
    }

    public function passwordRequirements(): array {
        return $this->service->passwordRequirements();
    }
}