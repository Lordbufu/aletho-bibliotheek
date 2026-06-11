<?php
namespace App\Libs\Context;

/** `locations` & `user_locations` db table */
final class LocationContext {
    public int      $loc_id;
    public string   $loc_name;
    public ?int     $user_id;
    public bool     $is_active;

    public function __construct(array $row) {
        $this->loc_id       = (int)$row['loc_id'];
        $this->loc_name     = $row['loc_name'];
        $this->user_id      = isset($row['user_id']) ? (int)$row['user_id'] : null;
        $this->is_active    = (bool) $row['is_active'];
    }

    public static function fromRow(array $row): self {
        return new self($row);
    }
}