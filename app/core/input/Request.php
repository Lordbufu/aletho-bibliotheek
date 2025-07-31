<?php
namespace App\Core\Input;

class Request {
    private array $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function getString(string $key, int $maxLength = 255): string {
        $value = trim($this->data[$key] ?? '');
        $value = filter_var($value, FILTER_SANITIZE_STRING);
        return mb_substr($value, 0, $maxLength);
    }

    public function getInt(string $key, int $default = 0): int {
        $value = $this->data[$key] ?? null;
        return filter_var($value, FILTER_VALIDATE_INT) !== false
            ? (int) $value
            : $default;
    }

    public function getEmail(string $key): ?string {
        $value = trim($this->data[$key] ?? '');
        return filter_var($value, FILTER_VALIDATE_EMAIL)
            ? $value
            : null;
    }

    public function getToken(string $key): ?string {
        $value = $this->data[$key] ?? '';
        return preg_match('/^[a-zA-Z0-9_\-]{10,}$/', $value)
            ? $value
            : null;
    }
}