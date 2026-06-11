<?php

namespace App\Libs;

use App\Libs\Context\BookContext;

final class BookRepo {
    private \App\Database   $db;
    
    public function __construct() {
        $this->db           = \App\App::getService('database');
    }

    /** Re-factored functions */

    // Re-factored Tested and working again
    /** API: Return all active books (raw domain data only) */
    public function findAllActiveBooks(): array {
        $rows = $this->db->query()->fetchAll("
            SELECT *
            FROM books
            WHERE is_active = 1
            ORDER BY book_title ASC
        ");

        return array_map(fn($r) => BookContext::fromRow($r), $rows);
    }

    /** Still needs a review/re-factor */
    // /** API: Find a book by exact title (CRUD-safe) */
    // public function findBookByTitle(string $title): ?BookContext {
    //     $row = $this->db->query()->fetchOne("
    //         SELECT *
    //         FROM books
    //         WHERE title = :title
    //         LIMIT 1
    //     ", ['title' => $title]);

    //     return $row ? new BookContext($row) : null;
    // }

    // /** API: Find book by id */
    // public function findBookById(int $bookId): ?BookContext {
    //     $row = $this->db->query()->fetchOne("
    //         SELECT *
    //         FROM books
    //         WHERE id = :id
    //         LIMIT 1
    //     ", ['id' => $bookId]);

    //     return $row ? new BookContext($row) : null;
    // }

    // /** API: Insert a new book record */
    // public function insertBook(string $title, int $officeId): int {
    //     $this->db->query()->run("
    //         INSERT INTO books (title, home_office, cur_office, active)
    //         VALUES (:title, :office, :office, 1)
    //     ", [
    //         'title'  => $title,
    //         'office' => $officeId
    //     ]);

    //     return (int)$this->db->query()->lastInsertId();
    // }

    // /** API: Reactivate a previously inactive book */
    // public function reactivateBook(int $bookId): void {
    //     $this->db->query()->run("
    //         UPDATE books
    //         SET active = 1
    //         WHERE id = :id
    //     ", ['id' => $bookId]);
    // }

    // /** API: Deactivate book in database */
    // public function deactivateBook(int $bookId): void {
    //     $this->db->query()->run("
    //         UPDATE books
    //         SET active = 0 
    //         WHERE id = :id
    //     ", ['id' => $bookId]);
    // }

    // /** API: Update the book's title */
    // public function updateBookTitle(int $bookId, string $title): void {
    //     $this->db->query()->run("
    //         UPDATE books
    //         SET title = :title
    //         WHERE id = :id
    //     ", [
    //         'title' => $title,
    //         'id'    => $bookId
    //     ]);
    // }

    // /** API: Update the book’s office (single-office model) */
    // public function setAllBookOffices(int $bookId, int $officeId): void {
    //     $this->db->query()->run("
    //         UPDATE books
    //         SET home_office = :office,
    //             cur_office  = :office
    //         WHERE id = :id
    //     ", [
    //         'office' => $officeId,
    //         'id'     => $bookId
    //     ]);
    // }

    // /** API: Update only the current office location (single-office model) */
    // public function updateCurBookOffice(int $bookId, int $officeId): void {
    //     $this->db->query()->run("
    //         UPDATE books
    //         SET cur_office = :office
    //         WHERE id = :id
    //     ", [
    //         'office' => $officeId,
    //         'id'     => $bookId
    //     ]);
    // }

    // /** API: Update the book its reservation meta data */
    // public function updateReservationDataForBook(int $bookId, array $data): void {
    //     $this->db->query()->run("
    //         UPDATE
    //             books
    //         SET
    //             resv_loaner_id  = :loanerId,
    //             resv_office_id  = :officeId,
    //             resv_created_at = :created,
    //             resv_expires_at = :expires
    //         WHERE
    //             id = :bookId
    //     ", [
    //         'loanerId'  => $data['resv_loaner_id'],
    //         'officeId'  => $data['resv_office_id'],
    //         'created'   => $data['resv_created_at'] ?? null,
    //         'expires'   => $data['resv_expires_at'] ?? null,
    //         'bookId'    => $bookId
    //     ]);
    // }
}