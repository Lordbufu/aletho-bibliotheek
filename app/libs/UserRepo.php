<?php
namespace App\Libs;

use App\Libs\Context\UserContext;

// Re-factor status: tested and working
final class UserRepo {
    private \App\Database   $db;

    public function __construct() {
        $this->db = \App\App::getService('database');
    }

    /** API: Find user by id or return null */
    public function findUserById(int $id): ?UserContext {
        $row = $this->db->query()->fetchOne("
            SELECT *
            FROM users
            WHERE user_id = :id
            LIMIT 1
        ", ['id' => $id]);

        return $row ? new UserContext($row) : null;
    }

    /** API: Find user by login identifier credentials */
    public function findByIdentifier(string $normalized): ?UserContext {
        $row = $this->db->query()->fetchOne("
            SELECT *
            FROM users
            WHERE LOWER(user_name) = :id
            OR LOWER(user_email) = :id
            LIMIT 1
        ", ['id' => $normalized]);

        return $row ? new UserContext($row) : null;
    }

    /** API: Update password for user id */
    public function updatePassword(int $userId, string $hash): void {
        $this->db->query()->run("
            UPDATE users
            SET user_password = :hash
            WHERE user_id = :id
        ", ['hash' => $hash, 'id' => $userId]);
    }
}