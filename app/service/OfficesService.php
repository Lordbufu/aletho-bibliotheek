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

    public function getOfficeIdByName(string $name): int {
        return $this->libs->offices()->getOfficeIdByName($name);
    }

    public function getOfficeNameByOfficeId(int $officeId): string {
        return $this->libs->offices()->getOfficeNameByOfficeId($officeId);
    }

    public function getOfficesForDisplay(): array {
        return $this->libs->offices()->getOfficesForDisplay();
    }

    public function getAdminsForOffices(int $officeId): array {
        return $this->libs->offices()->getAdminsForOffices($officeId);
    }

    public function getLinksByBookId(int $bookId): array {
        return $this->libs->offices()->getLinksByBookId($bookId);
    }

    // Potentially obsolete ?
    // public function getOfficeNamesByBookId(int $bookId): string {
    //     return $this->libs->offices()->getOfficeNamesByBookId($bookId);
    // }

    // TODO: Review if still required, i think this was added for future admin functions ?
    // public function addBookOffices(array $names, int $bookId): void {
    //     $this->libs->offices()->addBookOffices($names, $bookId);
    // }

    // public function updateBookOffices(int $bookId, array $offices): void {
    //     $this->libs->offices()->updateBookOffices($bookId, $offices);
    // }
}