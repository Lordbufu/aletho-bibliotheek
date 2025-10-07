<?php

namespace App\Libs;

use App\App;

class OfficeRepo {
    protected array $offices;
    protected array $links;
    protected array $userLinks;

    /** Get all offices table data.
     *      @return array
     */
    public function getAllOffices(): array {
        if (!isset($this->offices)) {
            $this->offices = App::getService('database')
                ->query()
                ->fetchAll("SELECT * FROM offices");
        }

        if (!is_array($this->offices) || $this->offices === []) {
            App::getService('logger')->warning(
                "The 'OfficeRepo' dint get any offices from the database",
                'bookservice'
            );
        }

        return $this->offices;
    }

    /** Get all book_office link table data (many-to-many relation).
     *      @return array
     */
    public function getAllLinks(): array {
        if (!isset($this->links)) {
            $this->links = App::getService('database')
                ->query()
                ->fetchAll("SELECT * FROM book_office");
        }

        if (!is_array($this->links) || $this->links === []) {
            App::getService('logger')->warning(
                "The 'OfficeRepo' dint get any office-links from the database",
                'bookservice'
            );
        }

        return $this->links;
    }

    /** Get all user_office table data
     *      @return array
     */
    public function getAllUserLinks(): array {
        if (!isset($this->userLinks)) {
            $this->userLinks = App::getService('database')
                ->query()
                ->fetchAll("SELECT * FROM user_office");
        }

        if (!is_array($this->userLinks) || $this->userLinks === []) {
            App::getService('logger')->warning(
                "The 'OfficeRepo' dint get any offices-links from the database",
                'bookservice'
            );
        }

        return $this->userLinks;
    }

    /** Return office name(s) for a book, based on office id for current relations.
     *      @param int $officeId The office ID.
     *      @return string
     */
    public function getOfficeNameByOfficeId(int $officeId): string {
        $mapNames = array_column($this->getAllOffices(), 'name', 'id');
        return $mapNames[$officeId] ?? 'Unknown';
    }

    /** Return office name(s) for a book, based on book id for many-to-many relations.
     *      @param int $bookId The book ID.
     *      @return string
     */
    public function getOfficeNamesByBookId(int $bookId): string {
        $mapNames = array_column($this->getAllOffices(), 'name', 'id');
        $links = $this->getAllLinks();
        $names = [];

        foreach ($links as $link) {
            if ((int)$link['book_id'] !== $bookId) {
                continue;
            }

            $names[] = $mapNames[$link['office_id']] ?? 'Unknown';
        }
        
        return implode(', ', $names);
    }
}