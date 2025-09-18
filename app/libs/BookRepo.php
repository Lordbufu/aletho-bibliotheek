<?php

namespace App\Libs;

use App\App;

/**
 * Very straight forward class, simply passing along database data.
 */
class BookRepo {
    /**
     * Simple get all book table data to caller.
     * @return array
     */
    public function findAll(): array {
        return App::getService('database')
            ->query()
            ->fetchAll("SELECT * FROM books");
    }

    /**
     * Simple get single book table data to caller.
     * @return array
     */
    public function findOne(int $id): array {
        return App::getService('database')
            ->query()
            ->fetchOne("SELECT * FROM books WHERE id = ?", [$id]);
    }
}