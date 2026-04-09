<?php
namespace App\Libs;

final class OfficesRepo {
    private \App\Database $db;

    public function __construct() {
        $this->db = \App\App::getService('database');
    }

    /** API: Get all office names and ids */
    public function getAllOffices(): array {
        $sql = "SELECT id, name FROM offices ORDER BY name ASC";
        return $this->db->query()->fetchAll($sql);
    }

    /** API: Check if a office exists */
    public function officeExists(int $id): bool {
        $row = $this->db->query()->fetchOne(
            "SELECT id FROM offices WHERE id = :id",
            ['id' => $id]
        );

        return (bool)$row;
    }

    /** API: Find office by name */
    public function findByName(string $name): ?array {
        $sql = "SELECT id, name FROM offices WHERE name = ?";
        return $this->db->query()->fetchOne($sql, [$name]);
    }

    /** API: Return office name based on its id */
    public function getOfficeName(int $id): ?string {
        $row = $this->db->query()->fetchOne(
            "SELECT name FROM offices WHERE id = :id",
            ['id' => $id]
        );

        return $row['name'] ?? null;
    }

    /** API: Fetch a whole batch of office names at once */
    public function getOfficeNamesForBooks(array $officeIds): array {
        if (!$officeIds) return [];

        $unique = array_unique($officeIds);

        $sql = "
            SELECT id, name
            FROM offices
            WHERE id IN (" . implode(',', $unique) . ")
        ";

        $rows = $this->db->query()->fetchAll($sql);

        $map = [];
        foreach ($rows as $row) {
            $map[(int)$row['id']] = $row['name'];
        }

        return $map;
    }
}
