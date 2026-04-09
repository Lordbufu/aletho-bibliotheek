<?php
namespace App\services;

use App\App;
use App\Libs\OfficesRepo;

class OfficesService {
    private OfficesRepo $offices;

    public function __construct() {
        $this->offices = new OfficesRepo();
    }

    /** Facade: Get all office names and ids */
    public function getAllOffices(): array {
        return $this->offices->getAllOffices();
    }

    /** Facade: Check if a office exists */
    public function officeExists(int $id) {
        return $this->offices->officeExists($id);
    }

    /** Facade: Find office by name */
    public function findByName(string $name): ?array {
        return $this->offices->findByName($name);
    }

    /** Facade: Return office name based on its id */
    public function getOfficeName(int $id): ?string {
        return $this->offices->getOfficeName($id);
    }

    /** Facade: Fetch a whole batch of office names at once */
    public function getOfficeNamesForBooks(array $officeIds): array {
        return $this->offices->getOfficeNamesForBooks($officeIds);
    }
}