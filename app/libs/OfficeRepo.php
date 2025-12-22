<?php

namespace App\Libs;

/** Repository for managing offices and their relations to books and users */
class OfficeRepo {
    protected \App\Database $db;

    public function __construct(\App\Database $db) {
        $this->db = $db;
    }

    // New re-factored Helper functions
    // TODO: If many to many relations are not requested, deleted/removed this from the codebase.
    /** Helper: Resolve office names/IDs into valid office IDs, creating if needed. */
    protected function _getOrCreateOfficeIds(array $names): array {
        if (empty($names)) return [];

        $offices    = $this->getAllOffices();
        $nameToId   = array_column($offices, null, 'name');
        $officesIds = [];

        foreach ($names as $name) {
            if (is_numeric($name)) {
                $officesIds[] = (int)$name;
                continue;
            }

            if (isset($nameToId[$name])) {
                $office     = $nameToId[$name];
                $officeId   = (int)$office['id'];

                if (isset($office['active']) && (int)$office['active'] === 0) {
                    $query = "UPDATE offices SET active = 1 WHERE id = ?";
                    $this->db->query()->run($query, [$officeId]);
                }

                $officesIds[] = $officeId;
                continue;
            }

            $query          = "INSERT INTO offices (name, active) VALUES (?, 1)";
            $this->db->query()->run($query, [$name]);
            $id             = $this->db->query()->lastInsertId();
            $officesIds[]   = $id;
        }

        return $officesIds;
    }

    /** API & Helper: Fetch offices */
    public function getAllOffices(): array {
        $query = "SELECT * FROM offices";
        return $this->db->query()->fetchAll($query);
    }

    /** Get office ID by name, or 0 if not found. */
    public function getOfficeIdByName(string $name): int {
        $query  = "SELECT id FROM offices WHERE name = ?";
        $row    = $this->db->query()->fetchOne($query, [$name]);
        return $row['id'] ?? 0;
    }

    /** Get office name by ID. */
    public function getOfficeNameByOfficeId(int $officeId): string {
        $offices    = $this->getAllOffices();
        $map        = array_column($offices, 'name', 'id');
        return $map[$officeId] ?? 'Unknown';
    }

    /** Get office names for a given book. */
    public function getOfficeNamesByBookId(int $bookId): string {
        $query  = "SELECT o.name FROM offices o JOIN book_office bo ON o.id = bo.office_id WHERE bo.book_id = ?";
        $rows   = $this->db->query()->fetchAll($query, [$bookId]);
        return implode(', ', array_column($rows, 'name'));
    }

    /** API: Get office names for the frontend */
    public function getOfficesForDisplay(): array {
        $offices    = $this->getAllOffices();
        $out        = [];

        foreach ($offices as $office) {
            if (!$office['active']) {
                continue;
            }

            $out[] = [
                'id' => $office['id'],
                'name' => $office['name']
            ];
        }

        return $out;
    }

    /** API: Match admins based on office location */
    public function getAdminsForOffices(int $officeId): array {
        $query = "SELECT u.id, u.name, u.email, uo.office_id FROM users u
                    INNER JOIN user_office uo ON u.id = uo.user_id
                    WHERE u.is_office_admin  = 1
                    AND uo.office_id = ?";

        return $this->db->query()->fetchAll($query, [$officeId]);
    }

    /** Get office IDs linked to a book. */
    public function getLinksByBookId(int $bookId): array {
        $query = "SELECT office_id FROM book_office WHERE book_id = ?";
        return $this->db->query()->fetchAll($query, [$bookId]);
    }

    // New re-factored API functions
    // TODO: Create schema table for `book_offices`, when many to many relations are requested, otherwhise this can be deleted/removed this from the codebase.
    /** API: Ensure all `offices` & `book_offices` data is correct, and the table isnt getting polluted over time */
    public function syncBookOffices(int $bookId, array $names): void {
        $newIds     = $this->_getOrCreateOfficeIds($names);
        $currentIds = array_column($this->getLinksByBookId($bookId), 'office_id');
        $toDelete   = array_diff($currentIds, $newIds);
        $toAdd      = array_diff($newIds, $currentIds);

        if (!empty($toDelete)) {
            $placeholders = implode(',', array_fill(0, count($toDelete), '?'));
            $query        = "DELETE FROM book_office WHERE book_id = ? AND office_id IN ($placeholders)";
            $params       = array_merge([$bookId], $toDelete);

            $this->db->query()->run($query, $params);
        }

        if (!empty($toAdd)) {
            $placeholders = implode(', ', array_fill(0, count($toAdd), '(?, ?)'));
            $values       = [];

            foreach ($toAdd as $oid) {
                $values[] = $bookId;
                $values[] = $oid;
            }

            $query = "INSERT INTO book_office (book_id, office_id) VALUES $placeholders";
            $this->db->query()->run($query, $values);
        }
    }
}

/** Old Obsolete functions, that have be replaced with better versions
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
 */