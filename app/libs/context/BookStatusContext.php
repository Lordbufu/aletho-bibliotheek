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
    public ?\DateTimeImmutable  $action_expires     = null;
    public bool                 $action_used;
    public bool                 $action_complete;

    /** Construct context based on row data. */
    public function __construct(array $row) {
        $this->bs_id            = (int) $row['bs_id'];
        $this->book_id          = (int) $row['book_id'];
        $this->status_id        = (int) $row['status_id'];
        $this->noti_send        = (bool) ($row['noti_send'] ?? false);
        $this->noti_send_at     = !empty($row['noti_send_at']) ? new \DateTimeImmutable($row['noti_send_at']): null;
        $this->bs_created_at    = new \DateTimeImmutable($row['bs_created_at']);
        $this->is_active        = (bool) $row['is_active'];
        $this->action_name      = $row['action_name'] ?? null;
        $this->action_token     = $row['action_token'] ?? null;
        $this->action_expires   = !empty($row['action_expires']) ? new \DateTimeImmutable($row['action_expires']) : null;
        $this->action_used      = (bool) ($row['action_used'] ?? false);
        $this->action_complete  = (bool) ($row['action_complete'] ?? false);
    }

    /** API: Return row as context. */
    public static function fromRow(array $row): self {
        return new self($row);
    }
}