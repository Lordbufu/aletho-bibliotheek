<?php
namespace App\Libs;

/** W.I.P. */
class UserRepo {
    protected ?array        $user = null;
    protected \App\Database $db;

    public function __construct(\App\Database $db) {
        $this->db = $db;
    }

    /** Helper: Cache user data from DB */
    protected function setUser(int $id): void {
        $query      = "SELECT * FROM users WHERE id = ?";
        $this->user = $this->db->query()->fetchOne($query, [$id]);
    }

    /** API: Fetch user object by ID */
    public function getUserById(int $id): array {
        if ($this->user === null) {
            $this->setUser($id);
        }

        return $this->user;
    }


    /** API: Get user datafield from DB */

    /** API: Update user data in DB */
}