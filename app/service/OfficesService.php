<?php
namespace App\Service;

use App\App;

class OfficesService {
    protected \App\Libraries    $libs;
    protected \App\Database     $db;

    public function __construct() {
        try {
            $this->libs = App::getLibraries();
            $this->db   = App::getService('database');
        } catch (\Throwable $t) {
            throw $t;
        }
    }

    public function getAllOffices(): array {
        return $this->libs->offices()->getAllOffices();
    }

    public function getOfficeIdByName(string $name): int {
        return $this->libs->offices()->getOfficeIdByName($name);
    }

    public function getOfficeNameByOfficeId(int $officeId): string {
        return $this->libs->offices()->getOfficeNameByOfficeId($officeId);
    }

    public function getOfficeNamesByBookId(int $bookId): string {
        return $this->libs->offices()->getOfficeNamesByBookId($bookId);
    }

    public function getOfficesForDisplay(): array {
        return $this->libs->offices()->getOfficesForDisplay();
    }

    public function getLinksByBookId(int $bookId): array {
        return $this->libs->offices()->getLinksByBookId($bookId);
    }

    public function addBookOffices(array $names, int $bookId): void {
        $this->libs->offices()->addBookOffices($names, $bookId);
    }

    public function updateBookOffices(int $bookId, array $offices): void {
        $this->libs->offices()->updateBookOffices($bookId, $offices);
    }
}