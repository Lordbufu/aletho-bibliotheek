<?php
namespace App\Libs\Context;

final class BookStatusContext {
    /** book_status table fields represeted as properties. */
    public int                  $bs_id;
    public int                  $book_id;
    public int                  $status_id;
    public bool                 $noti_send;
    public ?\DateTimeImmutable  $noti_send_at       = null;
    public \DateTimeImmutable   $bs_created_at;
    public bool                 $is_active;
    public ?string              $action_name        = null;
    public ?string              $action_token       = null;
    public ?\DateTimeImmutable  $token_expires      = null;
    public bool                 $token_used;
    public bool                 $action_complete;

    /** Construct context based on row data. */
    public function __construct(array $row) {
        $this->bs_id            = (int) $row['bs_id'];
        $this->book_id          = (int) $row['book_id'];
        $this->status_id        = (int) $row['status_id'];
        $this->bs_created_at    = $row['created_at'];
        $this->is_active        = (bool) $row['active'];
        $this->noti_send        = (bool)$row['bs_noti_send'];
        $this->noti_send_at     = $row['noti_send_at'] !== null ? (int) $row['token_expires'] : null;
        $this->action_name      = $row['action_type'] ?? null;
        $this->action_token     = $row['action_token'] ?? null;
        $this->token_expires    = $row['token_expires'] !== null ? (int) $row['token_expires'] : null;
        $this->token_used       = (bool) $row['token_used'];
        $this->action_complete  = (bool) $row['action_finished'];
    }

    /** API: Return row as context. */
    public static function fromRow(array $row): self {
        return new self($row);
    }
}