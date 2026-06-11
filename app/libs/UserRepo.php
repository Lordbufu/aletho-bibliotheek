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

    /** API: Find user by name or email, requires a join to fetch the correct office id */
        // public function findByUsernameOrEmail(string $identifier): ?UserContext {
        //     $sql = "
        //         SELECT * FROM users
        //         WHERE u.name = :id OR u.email = :id
        //         LIMIT 1
        //     ";

        //     $row = $this->db->query()->fetchOne($sql, ['id' => $identifier]);

        //     return $row ? $this->mapRowToUser($row) : null;
        // }

    /** API: Find admin by joined office_id, and filter by office admin and active tag */
        // public function findAdminByOfficeId(int $officeId): ?UserContext {
        //     $sql = "
        //         SELECT 
        //             u.id,
        //             u.name,
        //             u.email,
        //             u.is_office_admin,
        //             u.is_global_admin,
        //             u.active,
        //             o.office_id
        //         FROM users u
        //         INNER JOIN user_office o 
        //             ON o.user_id = u.id 
        //         AND o.active = 1
        //         WHERE o.office_id = :oId
        //         AND u.is_office_admin = 1
        //         AND u.active = 1
        //         LIMIT 1
        //     ";

        //     $row = $this->db->query()->fetchOne($sql, [
        //         'oId' => $officeId,
        //     ]);

        //     return $row ? $this->mapRowToUser($row, 'limited') : null;
        // }