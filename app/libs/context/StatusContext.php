<?php
namespace App\Libs\Context;

/** `status` db table */
final class StatusContext {
    public int      $status_id;
    public string   $status_name;
    public ?int     $status_length          = null;
    public ?int     $status_reminder        = null;
    public ?int     $status_overdue         = null;
    public int      $is_facade;
    public ?string  $filter_mode            = null;
    public ?array   $reservation_behavior   = null;
    public int      $is_active;

    /** Construct context based on row data. */
    public function __construct(array $row) {
        $this->status_id            = (int) $row['status_id'];
        $this->status_name          = $row['status_name'];
        $this->status_length        = $row['status_length'] !== null ? (int) $row['status_length'] : null;
        $this->status_reminder      = $row['status_reminder'] !== null ? (int) $row['status_reminder'] : null;
        $this->status_overdue       = $row['status_overdue'] !== null ? (int) $row['status_overdue'] : null;
        $this->is_facade            = (bool) $row['is_facade'];
        $this->filter_mode          = $row['filter_mode'];
        $this->reservation_behavior = $this->decodeJson($row['reservation_behavior']);
        $this->is_active            = (bool) $row['is_active'];
    }

    /** Helper: Convert DB string to a actual JSON array format. */
    private function decodeJson(?string $json): ?array {
        if ($json === null || trim($json) === '') {
            return null;
        }

        $decoded = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }
    
    /** API: Return row as context. */
    public static function fromRow(array $row): self {
        return new self($row);
    }
}