<?php
namespace App\Libs;

use App\Libs\Context\UserContext;

final class UserRepo {
    private \App\Database   $db;

    public function __construct() {
        $this->db = \App\App::getService('database');
    }

    /** Helper: Map data to object */
    private function mapRowToUser(array $row, string $filter = 'full'): UserContext {
        $u = new UserContext();

        $u->id           = (int)$row['id'];
        $u->username     = $row['name'];
        $u->email        = $row['email'];
        $u->active       = (bool)$row['active'];

        if ($filter === 'full') {
            $u->passwordHash = $row['password'];
        }

        if ((int)$row['is_global_admin'] === 1) {
            $u->role = 'global_admin';
        } elseif ((int)$row['is_office_admin'] === 1) {
            $u->role = 'office_admin';
        } elseif ((int)$row['is_loaner'] === 1) {
            $u->role = 'user';
        } else {
            $u->role = 'invalid';
        }

        $u->officeId = isset($row['office_id']) ? (int)$row['office_id'] : null;

        return $u;
    }

    /** API: Find user by id, requires a join to fetch the correct office id */
    public function findUserById(int $id): ?UserContext {
        $sql = "
            SELECT 
                u.id,
                u.name,
                u.email,
                u.password,
                u.is_loaner,
                u.is_office_admin,
                u.is_global_admin,
                u.active,
                o.office_id
            FROM users u
            LEFT JOIN user_office o ON o.user_id = u.id AND o.active = 1
            WHERE u.id = :id
            LIMIT 1
        ";

        $row = $this->db->query()->fetchOne($sql, ['id' => $id]);

        return $row ? $this->mapRowToUser($row) : null;
    }

    /** API: Find admin by joined office_id, and filter by office admin and active tag */
    public function findAdminByOfficeId(int $officeId): ?UserContext {
        $sql = "
            SELECT 
                u.id,
                u.name,
                u.email,
                u.is_office_admin,
                u.is_global_admin,
                u.active,
                o.office_id
            FROM users u
            INNER JOIN user_office o 
                ON o.user_id = u.id 
            AND o.active = 1
            WHERE o.office_id = :oId
            AND u.is_office_admin = 1
            AND u.active = 1
            LIMIT 1
        ";

        $row = $this->db->query()->fetchOne($sql, [
            'oId' => $officeId,
        ]);

        return $row ? $this->mapRowToUser($row, 'limited') : null;
    }

    /** API: Find user by name or email, requires a join to fetch the correct office id */
    public function findByUsernameOrEmail(string $identifier): ?UserContext {
        $sql = "
            SELECT 
                u.id,
                u.name,
                u.email,
                u.password,
                u.is_loaner,
                u.is_office_admin,
                u.is_global_admin,
                u.active,
                o.office_id
            FROM users u
            LEFT JOIN user_office o ON o.user_id = u.id AND o.active = 1
            WHERE u.name = :id OR u.email = :id
            LIMIT 1
        ";

        $row = $this->db->query()->fetchOne($sql, ['id' => $identifier]);

        return $row ? $this->mapRowToUser($row) : null;
    }

    /** API: Update password for user id */
    public function updatePassword(int $userId, string $hash): void {
        $sql = "UPDATE users SET password = :hash WHERE id = :id";
        $this->db->query()->run( $sql, ['hash' => $hash, 'id' => $userId]);
    }
}
