<?php
namespace App\Services;

use App\Libs\Context\LocationContext;
use App\ViewModel\LocationViewModel;

// Re-factor status: tested and working
class LocationService {
    private \App\Libs\LocationRepo $location;

    public function __construct() {
        $this->location = new \App\Libs\LocationRepo();
    }

    /** Facade: Get all raw location context */
    public function getAllLocations(): ?array {
        return $this->location->getAllLocations();
    }

    /** Facade: Get all active location context */
    public function getAllActiveLocations(): ?array {
        return $this->location->getAllLocations("active");
    }

    /** Facade: Get user its office location by user_id */
    public function getLocationByUserId(int $user_id): ?array {
        return $this->location->getLocationByUserId($user_id);
    }

    /** Facade: Get full location context for on loc_id */
    public function getLocationContextById(int $loc_id): ?LocationContext {
        return $this->location->getLocationContextById($loc_id);
    }

    /** Facade: Get location names for loc_id */
    public function getLocationNameById(int $loc_id): ?string {
        return $this->location->getLocationNameByIds($loc_id);
    }

    /** Facade: Fetch a whole batch of office names matching loc_ids */
    public function getLocationNamesForBooks(array $loc_ids): array {
        return $this->location->getLocationNameByIds($loc_ids);
    }

    /** API: Get locations for viewmodel */
    public function getLocationsForView(): array {
        return LocationViewModel::formatMany(
            $this->getAllActiveLocations()
        );
    }
}