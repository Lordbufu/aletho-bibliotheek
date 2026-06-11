<?php
namespace App\Libs\Context;

final class NotificationContext {
    /** `notifications` & `mail_template` table */
    public int      $noti_id;
    public string   $noti_name;
    public int      $noti_templ_id;
    public array    $noti_req_tokens;
    public ?array   $noti_opt_tokens;
    public int      $is_active;

    public function __construct(array $row) {
        $this->noti_id               = (int) $row['noti_id'];
        $this->noti_name             = $row['noti_name'];
        $this->noti_templ_id       = (int) $row['noti_templ_id'];
        $this->noti_req_tokens   = $this->decodeJson($row['noti_req_tokens']);
        if ($this->noti_req_tokens === null) {
            error_log(sprintf( '[NotificationContext] Invalid required_tokens JSON for notification ID %d (type: %s): %s',
                $this->noti_id,
                $this->noti_name,
                $row['noti_req_tokens']
            ));

            throw new \RuntimeException("Invalid required_tokens JSON for notification ID {$this->noti_id} ({$this->noti_name})");
        }

        $this->noti_opt_tokens   = $this->decodeJson($row['noti_opt_tokens']);
        $this->is_active           = (bool) $row['is_active'];
    }

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

    public static function fromRow(array $row): self {
        return new self($row);
    }
}