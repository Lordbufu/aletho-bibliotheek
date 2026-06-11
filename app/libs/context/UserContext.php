<?php
namespace App\Libs\Context;

/** `users` db table */
final class UserContext {
    public int                  $user_id;
    public string               $user_name;
    public string               $user_email;
    public string               $user_password;
    public ?array               $permission_flags;
    public \DateTimeImmutable   $user_created;
    public \DateTimeImmutable   $user_updated;
    public bool                 $is_active;

    /** Constructor for ease of use */
    public function __construct(array $row) {
        $this->user_id          = (int) $row['user_id'];
        $this->user_name        = $row['user_name'];
        $this->user_email       = $row['user_email'];
        $this->user_password    = $row['user_password'];
        $this->permission_flags = $this->decodeJson($row['permission_flags']);
        $this->user_created     = new \DateTimeImmutable($row['user_created']);
        $this->user_updated     = new \DateTimeImmutable($row['user_updated']);
        $this->is_active        = (bool) $row['is_active'];
    }

    /** Helper: Default decode function to proper prase the JSON/LONGTEXT permission field */
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

    // // Potentially not usefull for users (basically the same as construct, but can be used in loops on the caller side)
    // public static function fromRow(array $row): self {
    //     return new self($row);
    // }
}