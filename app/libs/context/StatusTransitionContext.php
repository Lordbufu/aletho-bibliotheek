<?php
namespace App\Libs\Context;

/** `book_reservations` db table */
final class StatusTransitionContext {
    public int      $st_id;
    public int      $from_status;
    public int      $to_status;
    public ?int     $noti_id                = null;
    public array    $context_requirements;
    public bool     $is_active;

    /** Constructor for ease of use */
    public function __construct(array $row) {
        $this->st_id                = $row['st_id'];
        $this->from_status          = $row['from_status_id'];
        $this->to_status            = $row['to_status_id'];
        $this->noti_id              = $row['noti_id'] ?? null;
        $this->context_requirements = $this->decodeJson($row['context_requirements']);
        $this->is_active            = $row['is_active'];
    }

    /** fromRow($row): To easily construct arrays of data */
    // public static function fromRow(array $row): self {}

    /** Helpers: decode the json string into an array */
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
}