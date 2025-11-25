<?php

namespace App;

use App\App;

class OfficesService {
    protected \App\Libs\OfficeRepo $offices;

    public function __construct() {
        try {
            $this->offices = new \App\Libs\OfficeRepo();
        } catch (\Throwable $t) {
            throw $t;
        }
    }

    public function getAllOffices(): array {
        return $this->offices->getAllOffices();
    }

    public function getOfficeIdByName(string $name): int {
        return $this->offices->getOfficeIdByName($name);
    }

    public function getOfficeNameByOfficeId(int $officeId): string {
        return $this->offices->getOfficeNameByOfficeId($officeId);
    }

    public function getOfficeNamesByBookId(int $bookId): string {
        return $this->offices->getOfficeNamesByBookId($bookId);
    }

    public function getLinksByBookId(int $bookId): array {
        return $this->offices->getLinksByBookId($bookId);
    }

    public function addBookOffices(array $names, int $bookId): void {
        $this->offices->addBookOffices($names, $bookId);
    }

    public function updateBookOffices(int $bookId, array $offices): void {
        $this->offices->updateBookOffices($bookId, $offices);
    }
}