<?php

namespace App\Libs;

use App\App;

/* Writers library, dealing with all writers table data & relations. */
class WriterRepo {
    protected array $writers;
    protected array $links;

    /** Get all writers as defined in the `writers` table.
     *      @return array
     */
    public function getAllWriters(): array {
        if (!isset($this->writers)) {
            $this->writers = App::getService('database')
                ->query()
                ->fetchAll("SELECT * FROM writers");
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
            $this->links = App::getService('database')
                ->query()
                ->fetchAll("SELECT * FROM book_writers");
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

    /** Update the writers for a given book (many-to-many), removes all existing links and inserts the new ones.
     *      @param int $bookId
     *      @param array $writers Array of writer names or IDs
     *      @return void
     */
    public function updateBookWriters(int $bookId, array $writers): void {
        // Remove all existing links for this book
        App::getService('database')
            ->query()
            ->run("DELETE FROM book_writers WHERE book_id = ?", [$bookId]);

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
                    App::getService('database')
                        ->query()
                        ->run("INSERT INTO writers (name) VALUES (?)", [$writer]);

                    $writerId = App::getService('database')
                        ->query()
                        ->lastInsertId();
                    $nameToId[$writer] = $writerId;
                }
            }

            App::getService('database')
                ->query()
                ->run(
                    "INSERT INTO book_writers (book_id, writer_id) VALUES (?, ?)",
                    [$bookId, $writerId]
            );
        }
    }
}