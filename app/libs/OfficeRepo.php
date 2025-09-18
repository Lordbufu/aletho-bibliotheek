<?php
namespace App\Libs;

use App\App;

class OfficeRepo {
    protected array $offices;
    protected array $userLinks;

    /**
     * 
     */
    public function getAllOffices(): array {
        if (!isset($this->offices)) {
            $this->offices = App::getService('database')
                ->query()
                ->fetchAll("SELECT * FROM offices");
        }

        return $this->offices;
    }

    /**
     * 
     */
    public function getAllUserLinks(): array {
        if (!isset($this->userLinks)) {
            $this->userLinks = App::getService('database')
                ->query()
                ->fetchAll("SELECT * FROM user_office");
        }

        return $this->userLinks;
    }

    /**
     * 
     */
    public function getOfficeNamesById(int $id): string {
        $mapNames = array_column($this->getAllOffices(), 'name', 'id');
        $names = $mapNames[$id] ?? 'Unknown';
        return $names;
    }
}