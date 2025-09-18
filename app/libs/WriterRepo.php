<?php

namespace App\Libs;

use App\App;

/**
 * 
 */
class WriterRepo {
    protected array $writers;
    protected array $links;

    /**
     * 
     */
    public function getAllWriters(): array {
        if (!isset($this->writers)) {
            $this->writers = App::getService('database')
                ->query()
                ->fetchAll("SELECT * FROM writers");
        }

        return $this->writers;
    }

    /**
     * 
     */
    public function getAllLinks(): array {
        if (!isset($this->links)) {
            $this->links = App::getService('database')
                ->query()
                ->fetchAll("SELECT * FROM book_writers");
        }

        return $this->links;
    }

    /**
     * 
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
}