<?php
namespace App\Libs;

/** W.I.P. */
class NotificationRepo {
    protected \App\Database $db;
    
    public function __construct(\App\Database $db) {
        $this->db = $db;
    }

    /** API: Get notification by type */
    public function getNotiIdByType(string $type) {
        $query = "SELECT * FROM notifications WHERE type = ? AND active = 1";
        $stmt = $this->db->query()->fetchOne($query, [$type]);
        return $stmt['id'] ?? null;
    }
}