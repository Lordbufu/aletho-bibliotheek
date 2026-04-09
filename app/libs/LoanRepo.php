<?php
namespace App\Libs;

use App\Libs\Context\LoanContext;

final class LoanRepo {
    private \App\Database $db;

    public function __construct() {
        $this->db = \App\App::getService('database');
    }

    /** Helper: Populate the LoanContext for the provided database row */
    private function mapRowToLoan(array $row): LoanContext {
        $ctx = new LoanContext();
        $ctx->id        = (int)$row['id'];
        $ctx->bookId    = (int)$row['book_id'];
        $ctx->loanerId  = (int)$row['loaner_id'];
        $ctx->statusId  = (int)$row['status_id'];
        $ctx->startDate = new \DateTimeImmutable($row['start_date']);
        $ctx->endDate   = $row['end_date'] ? new \DateTimeImmutable($row['end_date']) : null;
        $ctx->active = (bool)$row['active'];
        return $ctx;
    }

    /** API: Get current loan by status and book id */
    public function getCurrentLoanById(int $statusId, int $bookId): ?LoanContext {
        $row = $this->db->query()->fetchOne("
            SELECT *
            FROM book_loaners
            WHERE status_id = :sId
            AND book_id = :bId
            AND active = 1
        ", [
            'sId' => $statusId,
            'bId' => $bookId
        ]);

        return $row ? $this->mapRowToLoan($row) : null;
    }

    /** API: Get active loans for book */
    public function getActiveLoansForBook(int $bookId): ?LoanContext {
        $row = $this->db->query()->fetchOne("
            SELECT *
            FROM book_loaners
            WHERE book_id = :bId
            AND active = 1
        ", [
            'bId' => $bookId
        ]);

        return $row ? $this->mapRowToLoan($row) : null;
    }

    /** API: Get previous loaners by book id */
    public function getPreviousLoansByBookId(int $bookId): ?array {
        $rows = $this->db->query()->fetchAll("
            SELECT *
            FROM book_loaners
            WHERE book_id = :id
            AND active = 0
            ORDER BY end_date DESC
            LIMIT 5
        ", [
            'id' => $bookId,
        ]);

        return array_map([$this, 'mapRowToLoan'], $rows);
    }

    /** API: Create new book_loaners record for status transitions */
    public function createLoan(int $bookId, int $loanerId, int $statusId, ?\DateTimeImmutable $startDate, ?\DateTimeImmutable $endDate, bool $active): int {
        $this->db->query()->run("
            INSERT INTO book_loaners
                (book_id, loaner_id, status_id, start_date, end_date, active)
            VALUES
                (:book, :loaner, :sId, :start, :end, :active)
        ", [
            'book'      => $bookId,
            'loaner'    => $loanerId,
            'sId'       => $statusId,
            'start'     => $startDate ? $startDate->format('Y-m-d H:i:s') : null,
            'end'       => $endDate ? $endDate->format('Y-m-d H:i:s') : null,
            'active'    => $active ? 1 : 0
        ]);

        return (int)$this->db->query()->lastInsertId();
    }

    /** API: Update book_loaners record for status transitions */
    public function updateLoan(int $loanId, int $statusId, ?\DateTimeImmutable $startDate, ?\DateTimeImmutable $endDate, bool $active): void {
        $this->db->query()->run("
            UPDATE book_loaners
            SET status_id = :sId,
                start_date = :start,
                end_date = :end,
                active = :active
            WHERE id = :id
        ", [
            'sId'       => $statusId,
            'start'     => $startDate ? $startDate->format('Y-m-d H:i:s') : null,
            'end'       => $endDate ? $endDate->format('Y-m-d H:i:s') : null,
            'active'    => $active ? 1 : 0,
            'id'        => $loanId
        ]);
    }

    /** API: Deactive loan row for a specific loan */
    public function deactivateLoan(int $loanId): void {
        $this->db->query()->run("
            UPDATE book_loaners
            SET active = 0
            WHERE id = :id
        ", [ 'id'       => $loanId ]);
    }

    // No longer relevant, marked as redundant/obsolete
        // /** API: Get latstest loaner row for specific book */
        // public function getLatestLoanerRowForBook(int $bookId): ?LoanContext {
        //     $row = $this->db->query()->fetchOne("
        //         SELECT *
        //         FROM book_loaners
        //         WHERE book_id = :bId
        //         ORDER BY id DESC
        //         LIMIT 1
        //     ", ['bId' => $bookId]);

        //     return $row ? $this->mapRowToLoan($row) : null;
        // }


}