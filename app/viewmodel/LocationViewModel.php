<?php
namespace App\ViewModel;

final class LocationViewModel {
    public int      $id;    // PK for `locations`.`location_id`.
    public string   $naam;  // Derived from `locations`.`location_name`.

    public function __construct(array $data) {
        $this->id   = $data['loc_id'];
        $this->naam = $data['loc_name'];
    }

    public static function formatOne(array $locatie): self {
        return new self($locatie);
    }

    public static function formatMany(array $locaties): array {
        $formatted = [];

        foreach ($locaties as $locatie) {
            $format = [
                'loc_id'     => $locatie->loc_id,
                'loc_name'   => $locatie->loc_name
            ];

            $formatted[] = new self($format);
        }

        return $formatted;
    }
}