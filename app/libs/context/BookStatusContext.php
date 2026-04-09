<?php
namespace App\Libs\Context;

/** A light-weight collection of book_status related data for additional view and status flows */
final class BookStatusContext {
    // pure `book_status` table
    public int                  $bookStatusId;          // book_status.id
    public int                  $bookId;                // book_status.book_id
    public bool                 $active;                // book_status.active
    public ?string              $actionName;            // book_status.action_type
    public ?string              $actionToken;           // book_status.action_token
    public ?\DateTimeImmutable  $tokenExpires;          // book_status.token_expires
    public bool                 $tokenUsed;             // book_status.token_used
    public bool                 $actionFinished;        // book_status.finished
    public \DateTimeImmutable   $createdAt;             // book_status.created_at

    // minimal borrowed context data:
        // TODO: Review removing the book_id from the borrowd context, since its already part of the main context
    public ?array               $book       = null;     // populate from BookContext [id, home_off, cur_off]
    public ?array               $status     = null;     // populate from StatusContext [id, type]
}