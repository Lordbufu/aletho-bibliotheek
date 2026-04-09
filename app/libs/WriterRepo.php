<?php

namespace App\Libs;

/** Repository for managing writers and their many-to-many relation with books */
class WriterRepo {
    protected \App\Database $db;

    public function __construct(\App\Database $db) {
        $this->db = $db;
    }

    // New re-factored Helper functions
    /** Helper: resolve writer names/IDs into valid writer IDs */
    protected function _getOrCreateWriterIds(array $names): array {
        if (empty($names)) return [];

        $writers    = $this->getAllWriters();
        $nameToId   = array_column($writers, null, 'name');

        $writerIds = [];

        foreach ($names as $name) {
            if (is_numeric($name)) {
                $writerIds[] = (int)$name;
                continue;
            }

            if (isset($nameToId[$name])) {
                $writer     = $nameToId[$name];
                $writerId   = (int)$writer['id'];

                if ((int)$writer['active'] === 0) {
                    $this->db->query()->run("UPDATE writers SET active = 1 WHERE id = ?", [$writerId]);
                }
            } else {
                $this->db->query()->run("INSERT INTO writers (name, active) VALUES (?, 1)", [$name]);
                $writerId = $this->db->query()->lastInsertId();
            }

            $writerIds[] = $writerId;
        }

        return $writerIds;
    }

    /** Helper: Strip irrelevant data for frontend presentation */
    protected function formatWriterForDisplay($writer): array {
        return [
            'id'    => $writer['id'],
            'name'  => $writer['name']
        ];
    }

    /** API & Helper: Get all writers data from the 'writers' table */
    public function getAllWriters(): array {
        $query = "SELECT * FROM writers";
        return $this->db->query()->fetchAll($query);
    }

    /** Get all writer names */
    public function getWritersForDisplay(): array {
        $out        = [];
        $writers    = $this->getAllWriters();

        foreach ($writers as $writer) {
            if (!$writer['active']) {
                continue;
            }

            $out[] = $this->formatWriterForDisplay($writer);
        }

        return $out;
    }

    /** Get all writer names for a given book ID, uses a direct JOIN query for efficiency */
    public function getWriterNamesByBookId(int $bookId): string {
        $query = "SELECT w.name FROM writers w JOIN book_writers bw ON w.id = bw.writer_id WHERE bw.book_id = ?";
        $rows  = $this->db->query()->fetchAll($query, [$bookId]);
        return implode(', ', array_column($rows, 'name'));
    }

    /** Get writer link rows for a given book */
    public function getLinksByBookId(int $bookId): array {
        $query  = "SELECT writer_id FROM book_writers WHERE book_id = ?";
        $result = $this->db->query()->fetchAll($query,[$bookId]);
        return $result;
    }

    // New re-factored functions
    /** API: Ensure all `writers` & `book_writers` data is correct, and the table isnt getting polluted over time */
    public function syncBookWriters(int $bookId, array $writers): void {
        $newIds     = $this->_getOrCreateWriterIds($writers);
        $currentIds = array_column($this->getLinksByBookId($bookId), 'writer_id');
        $toDelete   = array_diff($currentIds, $newIds);
        $toAdd      = array_diff($newIds, $currentIds);

        if (!empty($toDelete)) {
            $placeholders   = implode(',', array_fill(0, count($toDelete), '?'));
            $query          = "DELETE FROM book_writers WHERE book_id = ? AND writer_id IN ($placeholders)";
            $params         = array_merge([$bookId], $toDelete);

            $this->db->query()->run($query, $params);
        }

        if (!empty($toAdd)) {
            $placeholders   = implode(', ', array_fill(0, count($toAdd), '(?, ?)'));
            $values         = [];
            foreach ($toAdd as $wid) {
                $values[]   = $bookId;
                $values[]   = $wid;
            }

            $query          =  "INSERT INTO book_writers (book_id, writer_id) VALUES $placeholders";
            $this->db->query()->run($query, $values);
        }
    }
}

/** Old Obsolete functions, that have be replaced with better versions
    // public function addBookWriters(int $bookId, array $names): void {
    //     if (empty($names)) return;

    //     $writerIds = $this->_getOrCreateWriterIds($names);
    //     $existingIds = array_column($this->getLinksByBookId($bookId), 'writer_id');

    //     $toAdd = array_diff($writerIds, $existingIds);
    //     if (!empty($toAdd)) {
    //         $placeholders = implode(', ', array_fill(0, count($toAdd), '(?, ?)'));
    //         $values = [];
    //         foreach ($toAdd as $wid) {
    //             $values[] = $bookId;
    //             $values[] = $wid;
    //         }
    //         $this->db->query()->run(
    //             "INSERT INTO book_writers (book_id, writer_id) VALUES $placeholders",
    //             $values
    //         );
    //     }
    // }

    // public function updateBookWriters(int $bookId, array $writers): void {
    //     $writerIds = $this->_getOrCreateWriterIds($writers);
    //     $currentIds = array_column($this->getLinksByBookId($bookId), 'writer_id');

    //     $toDelete = array_diff($currentIds, $writerIds);
    //     $toAdd = array_diff($writerIds, $currentIds);

    //     if (!empty($toDelete)) {
    //         $placeholders = implode(',', array_fill(0, count($toDelete), '?'));
    //         $this->db->query()->run(
    //             "DELETE FROM book_writers WHERE book_id = ? AND writer_id IN ($placeholders)",
    //             array_merge([$bookId], $toDelete)
    //         );
    //     }

    //     if (!empty($toAdd)) {
    //         $placeholders = implode(', ', array_fill(0, count($toAdd), '(?, ?)'));
    //         $values = [];
    //         foreach ($toAdd as $wid) {
    //             $values[] = $bookId;
    //             $values[] = $wid;
    //         }
    //         $this->db->query()->run(
    //             "INSERT INTO book_writers (book_id, writer_id) VALUES $placeholders",
    //             $values
    //         );
    //     }
    // }
 */