<?php

namespace App\Auth;

use App\App;

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
    private const REQUIRE_NUMBER  = true;
    /** Require at least one special character */
    private const REQUIRE_SPECIAL = true;

    /**
     * Check if a password meets all configured requirements.
     *
     * @param string $password
     * @return bool True if valid, false otherwise
     */
    public function isValid(string $password): bool {
        if (strlen($password) < self::MIN_LENGTH) {
            App::getService('logger')->warning("Password validation failed: too short", 'auth');
            return false;
        }

        if (self::REQUIRE_UPPER && !preg_match('/[A-Z]/', $password)) {
            App::getService('logger')->warning("Password validation failed: missing uppercase", 'auth');
            return false;
        }

        if (self::REQUIRE_LOWER && !preg_match('/[a-z]/', $password)) {
            App::getService('logger')->warning("Password validation failed: missing lowercase", 'auth');
            return false;
        }

        if (self::REQUIRE_NUMBER && !preg_match('/\d/', $password)) {
            App::getService('logger')->warning("Password validation failed: missing number", 'auth');
            return false;
        }

        if (self::REQUIRE_SPECIAL && !preg_match('/[\W_]/', $password)) {
            App::getService('logger')->warning("Password validation failed: missing special char", 'auth');
            return false;
        }

        return true;
    }

    /**
     * Get the current password requirements.
     *
     * @return array<string, mixed>
     */
    public function getRequirements(): array {
        return [
            'min_length'      => self::MIN_LENGTH,
            'require_upper'   => self::REQUIRE_UPPER,
            'require_lower'   => self::REQUIRE_LOWER,
            'require_number'  => self::REQUIRE_NUMBER,
            'require_special' => self::REQUIRE_SPECIAL,
            'descriptions'    => [
                'min_length'      => "At least " . self::MIN_LENGTH . " characters",
                'require_upper'   => "At least one uppercase letter",
                'require_lower'   => "At least one lowercase letter",
                'require_number'  => "At least one number",
                'require_special' => "At least one special character",
            ]
        ];
    }
}