<?php
namespace App\Libs;

use App\Libs\Context\{BookContext, StatusContext, BookStatusContext};
use App\Libs\Types\StatusType;

// Re-factor status: W.I.P.
final class BookStatusRepo {
    private \App\Database   $db;

    public function __construct() {
        $this->db = \App\App::getService('database');
    }

    // Re-factor status: tested and working
    /** API: Deactive specific status row */
    public function deactiveBookStatus(int $boek_id): void {
        $this->db->query()->run("UPDATE book_status SET is_active = 0 WHERE book_id = :id", ['id' => $boek_id]);
    }

    // Re-factor status: tested and working
    /** API: Set default book status by book_id, with optional status_id */
    public function setDefaultStatus(int $boek_id, int $status_id = 1): int {
        $sql = "INSERT INTO book_status (book_id, status_id, is_active, action_complete) VALUES (:book, :status, 1, 0)";

        $this->db->query()->run($sql, [
            'book'   => $boek_id,
            'status' => $status_id
        ]);

        return (int)$this->db->query()->lastInsertId();
    }

    // Re-factor status: tested and working
    /** API: Get all active book_status rows for a specific book */
    public function getActiveBookStatusForBook(int $boek_id): ?BookStatusContext {
        $row = $this->db->query()->fetchOne(
            "SELECT * FROM book_status WHERE book_id = :id AND is_active = 1",
            ['id' => $boek_id]
        );

        return $row ? new BookStatusContext($row) : null;
    }

    /** API: Create new status for status transition flows  */
        // public function createStatus(int $bookId, string $statusType, bool $active = true, ?string $actionToken = null, ?\DateTimeImmutable $tokenExpires = null): int {
        //     $statusId = StatusType::toId($statusType);

        //     $this->db->query()->run("
        //         INSERT INTO book_status
        //             (book_id, status_id, action_type, action_token, token_expires, token_used, action_finished, active)
        //         VALUES
        //             (:book, :status, NULL, :token, :expires, 0, 0, :active)
        //     ", [
        //         'book'    => $bookId,
        //         'status'  => $statusId,
        //         'token'   => $actionToken,
        //         'expires' => $tokenExpires ? $tokenExpires->format('Y-m-d H:i:s') : null,
        //         'active'  => $active ? 1 : 0
        //     ]);

        //     return (int)$this->db->query()->lastInsertId();
        // }

    /** API: Get the main active status for a book */
        // public function getActiveStatusForBook(int $bookId): ?array {
        //     return $this->db->query()->fetchOne("
        //         SELECT *
        //         FROM book_status
        //         WHERE book_id = :id
        //         AND active = 1
        //         ORDER BY created_at DESC
        //         LIMIT 1
        //     ", ['id' => $bookId]);
        // }

    /** API: Get action related fields based on row id */
        // public function getActionDataForRow(int $id): ?array {
        //     $row = $this->db->query()->fetchOne("
        //         SELECT 
        //             action_type, action_token, token_expires, token_used
        //         FROM
        //             book_status
        //         WHERE
        //             id = :id
        //         AND active = 1
        //     ", ['id' => $id]);

        //     return $row ?: null;
        // }

    /** API: Get book_id based on the row index */
        // public function getBookIdForRow($id): ?int {
        //     $row = $this->db->query()->fetchOne("
        //         SELECT 
        //             book_id
        //         FROM
        //             book_status
        //         WHERE
        //             id = :id
        //     ", ['id' => $id]);

        //     return $row['book_id'] ?: null;
        // }
}

// Does not belong here ?
    /** API: Get (all) status(es) for book(s) */
    // public function getStatusByBookId(?array $book_ids): ?array {
    //     if (!$book_ids) {
    //         return [];
    //     }

    //     // refactor for BookStatusContext, atm its copy and pasta code from the book_writers many-to-many relations
    //     if (!is_array($book_ids)) {
    //         $book_id = (int)$book_ids;
    //         $rows = $this->db->query()->fetchAll("
    //                 SELECT *
    //                 FROM status s
    //                 JOIN book_status bs
    //                     ON bs.status_id = s.status_id
    //                 WHERE bs.book_id = :book_id
    //             ", ["book_id" => $book_id]
    //         );
        
    //         return $rows ? array_map(fn($r) => BookStatusContext::fromRow($r), $rows) : null;
    //     }

    //     $book_ids = is_array($book_ids) ? $book_ids : [$book_ids];
    //     $book_ids = array_map('intval', $book_ids);

    //     $placeholders = [];
    //     $params = [];

    //     foreach ($book_ids as $i => $id) {
    //         $key = "book_id{$i}";
    //         $placeholders[] = ":{$key}";
    //         $params[$key] = $id;
    //     }

    //     $sql = "
    //         SELECT s.*, bs.book_id
    //         FROM status s
    //         JOIN book_status bs
    //             ON bs.status_id = s.status_id
    //         WHERE bs.book_id IN (" . implode(',', $placeholders) . ")
    //         ORDER BY bs.book_id
    //     ";

    //     $rows = $this->db->query()->fetchAll($sql, $params);

    //     if (!$rows) {
    //         return [];
    //     }

    //     $out = [];
    //     foreach ($rows as $r) {
    //         $bid = (int)$r['book_id'];
    //         $out[$bid][] = BookStatusContext::fromRow($r);
    //     }

    //     return $out;
    // }

// No longer relevant ?
    // public function hydrateBookStatusContext(BookContext $book, ?StatusContext $status, ?array $bookStatus): BookStatusContext {
    //     $ctx = new BookStatusContext();
    //     $ctx->bookStatusId      = (int)$bookStatus['id'];
    //     $ctx->active            = (bool)$bookStatus['active'];
    //     $ctx->actionName        = $bookStatus['action_type'];
    //     $ctx->actionToken       = $bookStatus['action_token'];
    //     $ctx->tokenExpires      = $bookStatus['token_expires'] ? new \DateTimeImmutable($bookStatus['token_expires']) : null;
    //     $ctx->tokenUsed         = (bool)$bookStatus['token_used'];
    //     $ctx->actionFinished    = (bool)$bookStatus['action_finished'];     // TODO: Adjust in 'Live' branch, seems like this prevents books from being added atm :S
    //     $ctx->createdAt         = new \DateTimeImmutable($bookStatus['created_at']);

    //     $ctx->book = [
    //         'id'           => $book->id,
    //         'homeOfficeId' => $book->homeOfficeId,
    //         'curOfficeId'  => $book->curOfficeId
    //     ];

    //     $ctx->status = [
    //         'id'            => $status->id,
    //         'type'          => $status->type,
    //         'periodLength'  => $status->periodLength
    //     ];

    //     return $ctx;
    // }

    // public function insertBookStatus(int $bookId, int $statusId): int {
    //     $sql = "
    //         INSERT INTO book_status (book_id, status_id, active, action_finished)
    //         VALUES (:book, :status, 1, 0)
    //     ";

    //     $this->db->query()->run($sql, [
    //         'book'   => $bookId,
    //         'status' => $statusId
    //     ]);

    //     return (int)$this->db->query()->lastInsertId();
    // }

    // public function finishActiveBookStatuses(int $bookId): void {
    //     $sql = "
    //         UPDATE book_status
    //         SET is_active = 0
    //         WHERE book_id = :book
    //         AND is_active = 1
    //     ";

    //     $this->db->query()->run($sql, ['book' => $bookId]);
    // }