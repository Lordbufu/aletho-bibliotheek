<?php
namespace App\Services;

use App\Libs\Context\UserContext;

// Re-factor status: tested and working
final class UserService {
    private \App\Libs\UserRepo  $user;

    public function __construct() {
        $this->user = new \App\Libs\UserRepo();
    }

    /** Facade: Find user by id and return as Context object */
    public function findUserById(int $id): ?UserContext {
        return $this->user->findUserById($id);
    }

    /** API: Change the password of the current office_admin */
    public function resetOwnPassword(int $adminId, string $oldPw, string $newPw): bool {
        $user = $this->user->findUserById($adminId);

        if (!$user) {
            return false;
        }

        if (!password_verify($oldPw, $user->user_password)) {
            return false;
        }

        $hash = password_hash($newPw, PASSWORD_DEFAULT);

        $this->user->updatePassword($adminId, $hash);

        return true;
    }

    /** API: Change the password of a specific user */
    public function resetPasswordForUser(int $adminId, string $userName, string $newPw): bool {
        $user = $this->user->findByIdentifier($userName);

        if (!$user) {
            return false;
        }

        $hash = password_hash($newPw, PASSWORD_DEFAULT);

        $this->user->updatePassword($user->user_id, $hash);

        return true;
    }
}