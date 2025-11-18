<?php

namespace App\Libs;

use App\App;


/** Repository for managing offices and their relations to books and users.
 *  Design notes:
 *      - Caches offices in memory for efficiency.
 *      - Does NOT cache book_office or user_office links globally (queries per entity instead).
 *      - Provides both additive (`addBookOffices`) and replace (`updateBookOffices`) flows.
 */
class OfficeRepo {
    protected ?array        $offices = null;
    protected \App\Database $db;

    public function __construct() {
        $this->db = App::getService('database');
    }

    /** Resolve office names/IDs into valid office IDs, creating if needed. */
    private function _getOrCreateOfficeIds(array $names): array {
        if (empty($names)) return [];

        $this->getAllOffices();
        $nameToId = array_column($this->offices, null, 'name');

        $ids = [];
        foreach ($names as $name) {
            if (is_numeric($name)) {
                $ids[] = (int)$name;
                continue;
            }

            if (isset($nameToId[$name])) {
                $ids[] = (int)$nameToId[$name]['id'];
            } else {
                $this->db->query()->run("INSERT INTO offices (name) VALUES (?)", [$name]);
                $id = $this->db->query()->lastInsertId();
                $new = ['id' => $id, 'name' => $name];
                $this->offices[] = $new;
                $nameToId[$name] = $new;
                $ids[] = $id;
            }
        }
        return $ids;
    }

    /** Get all offices (cached). */
    public function getAllOffices(): array {
        if ($this->offices === null) {
            $this->offices = $this->db->query()->fetchAll("SELECT * FROM offices");
        }
        return $this->offices;
    }

    /** Get office ID by name, or 0 if not found. */
    public function getOfficeIdByName(string $name): int {
        $row = $this->db->query()->fetchOne(
            "SELECT id FROM offices WHERE name = ?",
            [$name]
        );
        return $row['id'] ?? 0;
    }

    /** Get office name by ID. */
    public function getOfficeNameByOfficeId(int $officeId): string {
        $map = array_column($this->getAllOffices(), 'name', 'id');
        return $map[$officeId] ?? 'Unknown';
    }

    /** Get office names for a given book. */
    public function getOfficeNamesByBookId(int $bookId): string {
        $rows = $this->db->query()->fetchAll(
            "SELECT o.name
             FROM offices o
             JOIN book_office bo ON o.id = bo.office_id
             WHERE bo.book_id = ?",
            [$bookId]
        );
        return implode(', ', array_column($rows, 'name'));
    }

    /** Get office IDs linked to a book. */
    public function getLinksByBookId(int $bookId): array {
        return $this->db->query()->fetchAll(
            "SELECT office_id FROM book_office WHERE book_id = ?",
            [$bookId]
        );
    }

    /** Add offices to a book without removing existing ones. */
    public function addBookOffices(array $names, int $bookId): void {
        if (empty($names)) return;

        $ids = $this->_getOrCreateOfficeIds($names);
        $existing = array_column($this->getLinksByBookId($bookId), 'office_id');

        $toAdd = array_diff($ids, $existing);
        if (!empty($toAdd)) {
            $placeholders = implode(', ', array_fill(0, count($toAdd), '(?, ?)'));
            $values = [];
            foreach ($toAdd as $oid) {
                $values[] = $bookId;
                $values[] = $oid;
            }
            $this->db->query()->run(
                "INSERT INTO book_office (book_id, office_id) VALUES $placeholders",
                $values
            );
        }
    }

    /** Replace the set of offices for a book with a new set. */
    public function updateBookOffices(int $bookId, array $offices): void {
        $ids = $this->_getOrCreateOfficeIds($offices);
        $current = array_column($this->getLinksByBookId($bookId), 'office_id');

        $toDelete = array_diff($current, $ids);
        $toAdd = array_diff($ids, $current);

        if (!empty($toDelete)) {
            $placeholders = implode(',', array_fill(0, count($toDelete), '?'));
            $this->db->query()->run(
                "DELETE FROM book_office WHERE book_id = ? AND office_id IN ($placeholders)",
                array_merge([$bookId], $toDelete)
            );
        }

        if (!empty($toAdd)) {
            $placeholders = implode(', ', array_fill(0, count($toAdd), '(?, ?)'));
            $values = [];
            foreach ($toAdd as $oid) {
                $values[] = $bookId;
                $values[] = $oid;
            }
            $this->db->query()->run(
                "INSERT INTO book_office (book_id, office_id) VALUES $placeholders",
                $values
            );
        }
    }
}