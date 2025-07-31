<?php

namespace App\Core\Auth;

class UserPermissions {
    private array $user;

    public function setUser(array $user) {
        $this->user = $user;
        return $this;
    }

    public function isGlobalAdmin(): bool {
        return ! empty($this->user['is_global_admin']);
    }

    public function isOfficeAdmin(): bool {
        return ! empty($this->user['is_office_admin']);
    }

    public function canEdit(): bool {
        return $this->isGlobalAdmin() || $this->isOfficeAdmin();
    }

    public function canEditOffice(int $bookOfficeId): bool {
        return $this->isGlobalAdmin()
            || ($this->isOfficeAdmin()
                && isset($this->user['office_id'])
                && $this->user['office_id'] === $bookOfficeId
               );
    }
}