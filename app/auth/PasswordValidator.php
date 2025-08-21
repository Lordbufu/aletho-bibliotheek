<?php

namespace App\Auth;

class PasswordValidator {
    private const MIN_LENGTH      = 8;
    private const REQUIRE_UPPER   = true;
    private const REQUIRE_LOWER   = true;
    private const REQUIRE_NUMBER  = true;
    private const REQUIRE_SPECIAL = true;

    public static function isValid(string $password): bool {
        if (strlen($password) < self::MIN_LENGTH) {
            return false;
        }

        if (self::REQUIRE_UPPER && !preg_match('/[A-Z]/', $password)) {
            return false;
        }

        if (self::REQUIRE_LOWER && !preg_match('/[a-z]/', $password)) {
            return false;
        }

        if (self::REQUIRE_NUMBER && !preg_match('/\d/', $password)) {
            return false;
        }

        if (self::REQUIRE_SPECIAL && !preg_match('/[\W_]/', $password)) {
            return false;
        }

        return true;
    }

    public static function getRequirements(): array {
        return [
            'min_length' => self::MIN_LENGTH,
            'require_upper' => self::REQUIRE_UPPER,
            'require_lower' => self::REQUIRE_LOWER,
            'require_number' => self::REQUIRE_NUMBER,
            'require_special' => self::REQUIRE_SPECIAL,
        ];
    }
}