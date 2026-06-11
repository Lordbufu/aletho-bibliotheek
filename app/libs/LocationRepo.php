<?php
namespace App\Libs;

use App\Libs\Context\LocationContext;

// Re-factor status: tested and working
final class LocationRepo {
    private \App\Database $db;

    public function __construct() {
        $this->db = \App\App::getService('database');
    }

    /** API: Get all location context for specific mode
     *      @param string $mode = "all"|"active"
     */
    public function getAllLocations(string $mode = "all"): ?array {
        match ($mode){
            "all" => $rows = $this->db->query()->fetchAll("SELECT * FROM locations"),
            "active" => $rows = $this->db->query()->fetchAll("SELECT * FROM locations WHERE is_active = 1")
        };

        return $rows ? array_map(fn($r) => LocationContext::fromRow($r), $rows) : null;
    }

    /** API: Fetch location for user by user_id */
    public function getLocationByUserId(int $user_id): ?array {
        $sql = "
            SELECT *
            FROM locations l
            JOIN user_locations ul ON l.loc_id = ul.loc_id
            WHERE ul.user_id = :user_id
        ";

        $rows = $this->db->query()->fetchAll($sql, ['user_id' => $user_id]);

        return $rows ? array_map(fn($r) => LocationContext::fromRow($r), $rows) : null;
    }

    /** API: Get full location context by loc_id */
    public function getLocationContextById(int $loc_id): ?LocationContext {
        $row = $this->db->query()->fetchOne("
            SELECT * FROM locations WHERE loc_id = :loc_id",
            ['loc_id' => $loc_id]
        );
        
        return $row ? new LocationContext($row) : null;
    }

    /** API: Get single or multiple location name(s) for loc_id(s)
     *      @param int|int[]|null $loc_ids
     *      @return string|array|null
     */
    public function getLocationNameByIds(mixed $loc_ids): mixed {
        if (!$loc_ids) {
            return [];
        }

        if (!is_array($loc_ids)) {
            $loc_id = (int)$loc_ids;
            $row = $this->db->query()->fetchOne("
                SELECT loc_name FROM locations WHERE loc_id = :loc_id",
                ['loc_id' => $loc_id]
            );
            
            return $row ? $row['loc_name'] : null;
        }

        $loc_ids = array_map('intval', $loc_ids);

        $placeholders = [];
        $params = [];

        foreach ($loc_ids as $i => $id) {
            $key = "id{$i}";
            $placeholders[] = ":{$key}";
            $params[$key] = $id;
        }

        $sql = "
            SELECT loc_id, loc_name
            FROM locations
            WHERE loc_id IN (" . implode(',', $placeholders) . ")
        ";

        $rows = $this->db->query()->fetchAll($sql, $params);

        $out = [];
        foreach ($rows as $row) {
            $out[(int)$row['loc_id']] = $row['loc_name'];
        }

        return $out;
    }
}
