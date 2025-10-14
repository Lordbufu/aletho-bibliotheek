<?php

namespace App\Libs;

use App\{App, Database};

/* Writers library, dealing with all writers table data & relations. */
class WriterRepo {
    protected array $writers;
    protected array $links;
    protected Database $db;

    public function __construct($con = []) {
        if  (!empty($con)) {
            $this->db = $con;
        }
    }

    /** Get all writers as defined in the `writers` table.
     *      @return array
     */
    public function getAllWriters(): array {
        if (!isset($this->writers)) {
            $this->writers = $this->db->query()->fetchAll("SELECT * FROM writers");
        }

        if (!is_array($this->writers) || $this->writers === []) {
            App::getService('logger')->error(
                "The 'WriterRepo' dint get any writers from the database",
                'bookservice'
            );
        }

        return $this->writers;
    }

    /** Get all book_writers link table data (many-to-many relations).
     *      @return array
     */
    public function getAllLinks(): array {
        if (!isset($this->links)) {
            $this->links = $this->db->query()->fetchAll("SELECT * FROM book_writers");
        }

        if (!is_array($this->links) || $this->links === []) {
            App::getService('logger')->error(
                "The 'WriterRepo' dint get any writer-links from the database",
                'bookservice'
            );
        }

        return $this->links;
    }

    /** Get all writer names for a given book ID.
     *      @param int $bookId
     *      @return string Comma-separated writer names
     */
    public function getWriterNamesByBookId(int $bookId): string {
        $mapNames = array_column($this->getAllWriters(), 'name', 'id');
        $names = [];
        
        foreach ($this->getAllLinks() as $link) {
            if ((int)$link['book_id'] !== $bookId) {
                continue;
            }

            $names[] = $mapNames[$link['writer_id']] ?? 'Unknown';
        }

        return implode(', ', $names);
    }

    public function getLinksByBookId(int $bookId): array {
        return $this->db->query->fetchAll(
            "SELECT writer_id FROM book_writers WHERE book_id = ?",
            [$bookId]
        );
    }

    /**
     * 
     */
    public function addBookWriters(array $names, int $bookId) {
        if (empty($names)) {
            return;
        }

        if (!isset($this->writer)) {
            $this->getAllWriters();
        }

        // Map: name => [id, active]
        $nameToId = [];
        foreach ($this->writers as $writer) {
            $nameToId[$writer['name']] = [
                'id'     => (int)$writer['id'],
                'active' => (int)($writer['active'] ?? 1)
            ];
        }

        $writersIds = [];
        foreach ($names as $name) {
            if (isset($nameToId[$name])) {
                $writerId = $nameToId[$name]['id'];

                // Reactivate if inactive
                if ($nameToId[$name]['active'] === 0) {
                    $this->db->query()->run("UPDATE writers SET active = 1 WHERE id = ?", [$writerId]);
                    $nameToId[$name]['active'] = 1;
                }
            } else {
                // Insert new writer
                $this->db->query()->run("INSERT INTO writers (name, active) VALUES (?, 1)", [$name]);
                $writerId = $this->db->query()->lastInsertId();

                // Update local cache
                $nameToId[$name] = ['id' => $writerId, 'active' => 1];
                $this->writers[] = ['id' => $writerId, 'name' => $name, 'active' => 1];
            }

            $writersIds[] = $writerId;
        }

        // Now handle links
        $existingLinks = $this->getLinksByBookId($bookId);
        $existingIds = array_column($existingLinks, 'writer_id');

        foreach ($writersIds as $wId) {
            if (!in_array($wId, $existingIds, true)) {
                $this->db->query()->run(
                    "INSERT INTO book_writers (book_id, writer_id) VALUES (?, ?)",
                    [$bookId, $wId]
                );
            }
        }
    }

    /** Update the writers for a given book (many-to-many), removes all existing links and inserts the new ones.
     *      @param int $bookId
     *      @param array $writers Array of writer names or IDs
     *      @return void
     */
    public function updateBookWriters(int $bookId, array $writers): void {
        if (empty($writers)) {
            return;
        }

        // Make sure all `global` writers are set
        if (!isset($this->writers)) {
            $this->getAllWriters();
        }

        // Map writer names to IDs if needed
        $nameToId = array_column($this->writers, 'id', 'name');

        foreach ($writers as $writer) {
            if (is_numeric($writer)) {
                $writerId = $writer;
            } else {
                // Check if writer exists, else insert
                if (isset($nameToId[$writer])) {
                    $writerId = $nameToId[$writer];
                } else {
                    $this->db->query()->run(
                        "INSERT INTO writers (name) VALUES (?)",
                        [$writer]
                    );

                    $writerId = $this->db->query()->lastInsertId();
                    $nameToId[$writer] = $writerId;
                }
            }

            $this->db->query()->run(
                    "INSERT INTO book_writers (book_id, writer_id) VALUES (?, ?)",
                    [$bookId, $writerId]
            );
        }
    }
}