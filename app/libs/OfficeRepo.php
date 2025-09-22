<?php
namespace App\Libs;

use App\App;

class OfficeRepo {
    protected array $offices;
    protected array $userLinks;

    /**
     * Get -> Set all `offices` table data.
     */
    public function getAllOffices(): array {
        if (!isset($this->offices)) {
            $this->offices = App::getService('database')
                ->query()
                ->fetchAll("SELECT * FROM offices");
        }

        if (!is_array($this->offices) || $this->offices === []) {
            App::getService('logger')->error(
                "The 'OfficeRepo' dint get any offices from the database",
                'bookservice'
            );
        }

        return $this->offices;
    }

    /**
     * Get -> Set all `user_office` table data
     */
    public function getAllUserLinks(): array {
        if (!isset($this->userLinks)) {
            $this->userLinks = App::getService('database')
                ->query()
                ->fetchAll("SELECT * FROM user_office");
        }

        if (!is_array($this->userLinks) || $this->userLinks === []) {
            App::getService('logger')->error(
                "The 'OfficeRepo' dint get any offices-links from the database",
                'bookservice'
            );
        }

        return $this->userLinks;
    }

    /**
     * Return the users office name
     */
    public function getOfficeNamesById(int $id): string {
        $mapNames = array_column($this->getAllOffices(), 'name', 'id');
        $names = $mapNames[$id] ?? 'Unknown';
        return $names;
    }
}