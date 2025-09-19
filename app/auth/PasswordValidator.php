<?php

namespace App\Auth;

/**
 * Validates password strength against configurable rules.
 */
class PasswordValidator {
    /** Minimum allowed password length */
    private const MIN_LENGTH      = 8;
    /** Require at least one uppercase letter */
    private const REQUIRE_UPPER   = true;
    /** Require at least one lowercase letter */
    private const REQUIRE_LOWER   = true;
    /** Require at least one numeric digit */
    private const REQUIRE_DIGIT   = true;
    /** Require at least one special character */
    private const REQUIRE_SPECIAL = false;

    /**
     * Validate password against all requirements.
     */
    public function isValid(string $password): bool {
        if (strlen($password) < self::MIN_LENGTH) {
            return false;
        }
        if (self::REQUIRE_UPPER && !preg_match('/[A-Z]/', $password)) {
            return false;
        }
        if (self::REQUIRE_LOWER && !preg_match('/[a-z]/', $password)) {
            return false;
        }
        if (self::REQUIRE_DIGIT && !preg_match('/\d/', $password)) {
            return false;
        }
        if (self::REQUIRE_SPECIAL && !preg_match('/[\W_]/', $password)) {
            return false;
        }
        return true;
    }

    /**
     * Return current password requirements for display.
     */
    public function getRequirements(): array {
        return [
            'min_length'      => self::MIN_LENGTH,
            'require_upper'   => self::REQUIRE_UPPER,
            'require_lower'   => self::REQUIRE_LOWER,
            'require_digit'   => self::REQUIRE_DIGIT,
            'require_special' => self::REQUIRE_SPECIAL,
        ];
    }
}