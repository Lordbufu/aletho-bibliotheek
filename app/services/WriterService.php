<?php
namespace App\Services;

use App\Libs\WriterRepo;
use App\ViewModel\WriterViewModel;

// Re-factor status: Tested and working
final class WriterService {
    private WriterRepo $writer;
    
    public function __construct() {
        $this->writer = new \App\Libs\WriterRepo();
    }

    /** Facade: Get all writers stored in the database */
    public function getAllWriters(): ?array {
        return $this->writer->getAllWriters();
    }

    /** Facade: Get all active writers stored in the database */
    public function getAllActiveWriters(): ?array {
        return $this->writer->getAllWriters("active");
    }

    /** Facade: Get writer(s) for book_id(s)
     *      @param int|int[]|null $book_ids
     *      @return string|array|null
     */
    public function getBookWritersByBookId(mixed $book_ids): mixed {
        return $this->writer->getBookWritersByBookId($book_ids);
    }

    /** API: Get all active writers for viewmodel */
    public function getWritersForView(): array {
        return WriterViewModel::formatMany($this->getAllActiveWriters());
    }
}