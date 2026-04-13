<?php
namespace App\Libs;

use App\Libs\Context\{BookContext, StatusContext, BookStatusContext};
use App\Libs\Types\StatusType;

final class BookStatusRepo {
    private \App\Database   $db;

    public function __construct() {
        $this->db = \App\App::getService('database');
    }

    /** API: Hydrate the BookStatusContext. */
    public function hydrateBookStatusContext(BookContext $book, ?StatusContext $status, ?array $bookStatus): BookStatusContext {
        $ctx = new BookStatusContext();
        $ctx->bookStatusId      = (int)$bookStatus['id'];
        $ctx->active            = (bool)$bookStatus['active'];
        $ctx->actionName        = $bookStatus['action_type'];
        $ctx->actionToken       = $bookStatus['action_token'];
        $ctx->tokenExpires      = $bookStatus['token_expires'] ? new \DateTimeImmutable($bookStatus['token_expires']) : null;
        $ctx->tokenUsed         = (bool)$bookStatus['token_used'];
        $ctx->actionFinished    = (bool)$bookStatus['action_finished'];
        $ctx->createdAt         = new \DateTimeImmutable($bookStatus['created_at']);

        $ctx->book = [
            'id'           => $book->id,
            'homeOfficeId' => $book->homeOfficeId,
            'curOfficeId'  => $book->curOfficeId
        ];

        $ctx->status = [
            'id'            => $status->id,
            'type'          => $status->type,
            'periodLength'  => $status->periodLength
        ];

        return $ctx;
    }

    /** API: Get the main active status for a book */
    public function getActiveStatusForBook(int $bookId): ?array {
        return $this->db->query()->fetchOne("
            SELECT *
            FROM book_status
            WHERE book_id = :id
            AND active = 1
            ORDER BY created_at DESC
            LIMIT 1
        ", ['id' => $bookId]);
    }

    /** API: Get all active book_status rows for a specific book */
    public function getAllActiveBookStatusForBook(int $bookId): ?array {
        $rows = $this->db->query()->fetchAll("
            SELECT *
            FROM book_status
            WHERE book_id = :id
            AND active = 1
        ", ['id' => $bookId]);

        return $rows ?: null;
    }

    /** API: Get action related fields based on row id */
    public function getActionDataForRow(int $id): ?array {
        $row = $this->db->query()->fetchOne("
            SELECT 
                action_type, action_token, token_expires, token_used
            FROM
                book_status
            WHERE
                id = :id
            AND active = 1
        ", ['id' => $id]);

        return $row ?: null;
    }

    /** API: Get book_id based on the row index */
    public function getBookIdForRow($id): ?int {
        $row = $this->db->query()->fetchOne("
            SELECT 
                book_id
            FROM
                book_status
            WHERE
                id = :id
        ", ['id' => $id]);

        return $row['book_id'] ?: null;
    }

    /** API: Insert a new status row */
    public function insertBookStatus(int $bookId, int $statusId): int {
        $sql = "
            INSERT INTO book_status (book_id, status_id, active, action_finished)
            VALUES (:book, :status, 1, 0)
        ";

        $this->db->query()->run($sql, [
            'book'   => $bookId,
            'status' => $statusId
        ]);

        return (int)$this->db->query()->lastInsertId();
    }

    /** API: Mark all active statuses for a book as finished */
    public function finishActiveBookStatuses(int $bookId): void {
        $sql = "
            UPDATE book_status
            SET active = 0
            WHERE book_id = :book
            AND active = 1
        ";

        $this->db->query()->run($sql, ['book' => $bookId]);
    }

    /** API: Deactive specific status row */
    public function deactiveBookStatus($id): void {
        $sql = "
            UPDATE book_status
            set active = 0
            WHERE id = :id
        ";

        $this->db->query()->run($sql, ['id' => $id]);
    }

    /** API: Create new status for status transition flows  */
    public function createStatus(int $bookId, string $statusType, bool $active = true, ?string $actionToken = null, ?\DateTimeImmutable $tokenExpires = null): int {
        $statusId = StatusType::toId($statusType);

        $this->db->query()->run("
            INSERT INTO book_status
                (book_id, status_id, action_type, action_token, token_expires, token_used, action_finished, active)
            VALUES
                (:book, :status, NULL, :token, :expires, 0, 0, :active)
        ", [
            'book'    => $bookId,
            'status'  => $statusId,
            'token'   => $actionToken,
            'expires' => $tokenExpires ? $tokenExpires->format('Y-m-d H:i:s') : null,
            'active'  => $active ? 1 : 0
        ]);

        return (int)$this->db->query()->lastInsertId();
    }
}