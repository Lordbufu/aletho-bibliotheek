<?php
namespace App\Services;

use App\Libs\WriterRepo;
use App\Libs\Context\WritersContext;
use App\ViewModel\WriterViewModel;

// Re-factor status: W.I.P.
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

    // Concept code.
    /** API: Resolve writers for the book CRUD actions */
        // public function resolveWriters(array $writers): array {
        //     $resolved = [];

        //     foreach ($writers as $w) {
        //         if (is_int($w)) {
        //             // Existing writer
        //             $resolved[] = $w;
        //             continue;
        //         }

        //         // New writer
        //         $existing = $this->writersRepo->findByName($w);

        //         if ($existing) {
        //             $resolved[] = $existing->id;
        //         } else {
        //             $newId = $this->writersRepo->insert($w);
        //             $resolved[] = $newId;
        //         }
        //     }

        //     return $resolved;
        // }

    /** TODO List:
     *      - Review if i need a specific look-up to return only writer names for display/add/remove logic
     *      - Review how im going to deal with adding/linking writer data, for example: if writer isnt in DB add to `writers` else add link to `book_writers` etc.
     *      - Review how im going to deal with 'syncing' writer data.
     */
}