<?php

namespace App\Libs;

use App\App;

class OfficeRepo {
    protected array $offices;
    protected array $links;
    protected array $userLinks;

    /**
     * Get all offices table data.
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
     * Get all book_office link table data (many-to-many relation).
     */
    public function getAllLinks(): array {
        if (!isset($this->links)) {
            $this->links = App::getService('database')
                ->query()
                ->fetchAll("SELECT * FROM book_office");
        }

        if (!is_array($this->links) || $this->links === []) {
            App::getService('logger')->error(
                "The 'OfficeRepo' dint get any office-links from the database",
                'bookservice'
            );
        }

        return $this->links;
    }

    /**
     * Get all user_office table data
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
     * Return office name(s) for a book. Supports many-to-many if book_office exists, else falls back to office_id in books table.
     */
    public function getOfficeNamesById(int $officeIdOrBookId): string {
        $mapNames = array_column($this->getAllOffices(), 'name', 'id');
        $links = $this->getAllLinks();
        $names = [];

        // If book_office table exists and has links, use many-to-many
        if (is_array($links) && count($links) > 0) {
            foreach ($links as $link) {
                if ((int)$link['book_id'] !== $officeIdOrBookId) {
                    continue;
                }
                $names[] = $mapNames[$link['office_id']] ?? 'Unknown';
            }
            return implode(', ', $names);
        }
        
        // Else, treat $officeIdOrBookId as office_id from books table
        return $mapNames[$officeIdOrBookId] ?? 'Unknown';
    }
}