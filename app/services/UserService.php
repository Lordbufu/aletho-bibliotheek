<?php
namespace App\Services;

use App\Libs\UserRepo;
use App\Libs\Context\UserContext;

final class UserService {
    private UserRepo $users;

    public function __construct() {
        $this->users = new UserRepo();
    }

    /** Facade: Find user by id and return as Context object */
    public function findUserById(int $id): ?UserContext {
        return $this->users->findUserById($id);
    }

    /** Facade: Find admin by office id and office admin flag */
    public function findAdminByOfficeId(int $officeId): ?UserContext {
        return $this->users->findAdminByOfficeId($officeId);
    }

    /** API: Change the password of the current office_admin */
    public function resetOwnPassword(int $adminId, string $oldPw, string $newPw): bool {
        $user = $this->users->findUserById($adminId);

        if (!$user) {
            return false;
        }

        if (!password_verify($oldPw, $user->passwordHash)) {
            return false;
        }

        // Optional: enforce password policy here
        // e.g. min length, complexity, etc.

        $hash = password_hash($newPw, PASSWORD_DEFAULT);

        $this->users->updatePassword($adminId, $hash);

        // Optional: log admin action
        // $this->audit->logPasswordReset($adminId, $adminId);

        return true;
    }

    /** API: Change the password of a specific user */
    public function resetPasswordForUser(int $adminId, string $userName, string $newPw): bool {
        $user = $this->users->findByUsernameOrEmail($userName);

        if (!$user) {
            return false;
        }

        // Optional: enforce password policy here
        // e.g. min length, complexity, etc.

        $hash = password_hash($newPw, PASSWORD_DEFAULT);

        $this->users->updatePassword($user->id, $hash);

        // Optional: log admin action
        // $this->audit->logPasswordReset($adminId, $user['id']);

        return true;
    }
}