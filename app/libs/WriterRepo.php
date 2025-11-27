<?php

namespace App\Libs;

use App\App;

/** Repository for managing writers and their many-to-many relation with books */
class WriterRepo {
    protected ?array        $writers = null;
    protected \App\Database $db;

    public function __construct(\App\Database $db) {
        $this->db = $db;
    }

    /** Helper: resolve writer names/IDs into valid writer IDs */
    protected function _getOrCreateWriterIds(array $names): array {
        if (empty($names)) return [];

        $this->getAllWriters(); // ensure cache loaded
        $nameToId = array_column($this->writers, null, 'name');

        $writerIds = [];
        foreach ($names as $name) {
            if (is_numeric($name)) {
                $writerIds[] = (int)$name;
                continue;
            }

            if (isset($nameToId[$name])) {
                $writer = $nameToId[$name];
                $writerId = (int)$writer['id'];

                if ((int)($writer['active'] ?? 1) === 0) {
                    $this->db->query()->run("UPDATE writers SET active = 1 WHERE id = ?", [$writerId]);
                    $this->writers[array_search($writerId, array_column($this->writers, 'id'))]['active'] = 1;
                }
            } else {
                $this->db->query()->run("INSERT INTO writers (name, active) VALUES (?, 1)", [$name]);
                $writerId = $this->db->query()->lastInsertId();
                $newWriter = ['id' => $writerId, 'name' => $name, 'active' => 1];
                $this->writers[] = $newWriter;
                $nameToId[$name] = $newWriter;
            }

            $writerIds[] = $writerId;
        }

        return $writerIds;
    }

    /** Helper: Set global $writers */
    protected function setWriters() {
        $query = "SELECT * FROM writers";
        $this->writers = $this->db->query()->fetchAll($query);
    }

    /** Format writers for display */
    protected function formatWriterForDisplay($writer): array {
        return [
            'id' => $writer['id'],
            'name' => $writer['name']
        ];
    }

    /** Get all writers data from the 'writers' table */
    public function getAllWriters(): array {
        if ($this->writers === null) {
            $this->setWriters();
        }

        return $this->writers;
    }

    /** Get all writer names */
    public function getWritersForDisplay(): array {
        $out = [];

        if ($this->writers === null) {
            $this->setWriters();
        }

        foreach ($this->writers as $writer) {
            if (!$writer['active']) {
                continue;
            }

            $out[] = $this->formatWriterForDisplay($writer);
        }

        return $out;
    }

    /** Get all writer names for a given book ID, uses a direct JOIN query for efficiency */
    public function getWriterNamesByBookId(int $bookId): string {
        $rows = $this->db->query()->fetchAll(
            "SELECT w.name
             FROM writers w
             JOIN book_writers bw ON w.id = bw.writer_id
             WHERE bw.book_id = ?",
            [$bookId]
        );
        return implode(', ', array_column($rows, 'name'));
    }

    /** Get writer link rows for a given book */
    public function getLinksByBookId(int $bookId): array {
        return $this->db->query()->fetchAll(
            "SELECT writer_id FROM book_writers WHERE book_id = ?",
            [$bookId]
        );
    }

    /** Add writers to a book without removing existing ones, only inserts missing links; does not delete */
    public function addBookWriters(array $names, int $bookId): void {
        if (empty($names)) return;

        $writerIds = $this->_getOrCreateWriterIds($names);
        $existingIds = array_column($this->getLinksByBookId($bookId), 'writer_id');

        $toAdd = array_diff($writerIds, $existingIds);
        if (!empty($toAdd)) {
            $placeholders = implode(', ', array_fill(0, count($toAdd), '(?, ?)'));
            $values = [];
            foreach ($toAdd as $wid) {
                $values[] = $bookId;
                $values[] = $wid;
            }
            $this->db->query()->run(
                "INSERT INTO book_writers (book_id, writer_id) VALUES $placeholders",
                $values
            );
        }
    }

    /** Replace the set of writers for a book with a new set, uses diffing to minimize DB churn (only deletes/inserts changes) */
    public function updateBookWriters(int $bookId, array $writers): void {
        $writerIds = $this->_getOrCreateWriterIds($writers);
        $currentIds = array_column($this->getLinksByBookId($bookId), 'writer_id');

        $toDelete = array_diff($currentIds, $writerIds);
        $toAdd = array_diff($writerIds, $currentIds);

        if (!empty($toDelete)) {
            $placeholders = implode(',', array_fill(0, count($toDelete), '?'));
            $this->db->query()->run(
                "DELETE FROM book_writers WHERE book_id = ? AND writer_id IN ($placeholders)",
                array_merge([$bookId], $toDelete)
            );
        }

        if (!empty($toAdd)) {
            $placeholders = implode(', ', array_fill(0, count($toAdd), '(?, ?)'));
            $values = [];
            foreach ($toAdd as $wid) {
                $values[] = $bookId;
                $values[] = $wid;
            }
            $this->db->query()->run(
                "INSERT INTO book_writers (book_id, writer_id) VALUES $placeholders",
                $values
            );
        }
    }
}