<?php

namespace App\Libs;

use App\Libs\Context\BookContext;

// Re-factor status: Tested and working again
final class BookRepo {
    private \App\Database   $db;
    
    public function __construct() {
        $this->db           = \App\App::getService('database');
    }

    /** API: Return all active books */
    public function findAllActiveBooks(): array {
        $rows = $this->db->query()->fetchAll("SELECT * FROM books WHERE is_active = 1 ORDER BY book_title ASC");
        return array_map(fn($r) => BookContext::fromRow($r), $rows);
    }

    /** API: Find book by id */
    public function findBookById(int $boek_id): ?BookContext {
        $row = $this->db->query()->fetchOne("SELECT * FROM books WHERE book_id = :id",
            ['id' => $boek_id]
        );

        return $row ? new BookContext($row) : null;
    }

    /** API: Find a book by exact title */
    public function findBooksByTitle(string $title): ?array {
        $rows = $this->db->query()->fetchAll("
            SELECT *
            FROM books
            WHERE LOWER(TRIM(book_title)) = LOWER(TRIM(:title))
        ", ['title' => $title]);

        if (!$rows) {
            return null;
        }

        return array_map(
            fn($r) => BookContext::fromRow($r),
            $rows
        );
    }

    /** API: Reactivate a previously inactive book */
    public function reactivateBook(int $boek_id): void {
        $this->db->query()->run("UPDATE books SET is_active = 1 WHERE book_id = :id",
            ['id' => $boek_id]
        );
    }

    /** API: Insert a new book record */
    public function insertBook(string $title, int $locatie_id): int {
        $this->db->query()->run(" INSERT INTO books (book_title, book_home_loc, book_cur_loc, is_active) VALUES (:title, :locatie, :locatie, 1)",
        [   'title'  => $title,
            'locatie' => $locatie_id]
        );

        return (int)$this->db->query()->lastInsertId();
    }

    /** API: Deactivate book in database */
    public function deactivateBook(int $boek_id): void {
        $this->db->query()->run("UPDATE books SET is_active = 0 WHERE book_id = :id", ['id' => $boek_id]);
    }

    /** API: Update the book's title */
    public function updateBookTitle(int $boek_id, string $title): void {
        $this->db->query()->run(
            "UPDATE books SET book_title = :title WHERE book_id = :id",
            [   'title' => $title,
                'id'    => $boek_id ]
        );
    }

    /** API: Update the book’s office */
    public function setAllBookOffices(int $boek_id, int $locatie_id): void {
        $this->db->query()->run(
            "UPDATE books SET book_home_loc = :locatie, book_cur_loc  = :locatie WHERE book_id = :id",
            [   'locatie' => $locatie_id,
                'id'     => $boek_id ]
        );
    }

    // Re-factor status: W.I.P. (should work as expected though)
    // /** API: Update the book's home location only */
    // public function updateBookHomeLoc(int $boek_id, int $locatie_id): void {
    //     $this->db->query()->run(
    //         "UPDATE books SET book_home_loc = :loc WHERE book_id = :id",
    //         [   'loc'   => $locatie_id,
    //             'id'    => $boek_id ]
    //     );
    // }

    /** Still needs a review/re-factor */
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