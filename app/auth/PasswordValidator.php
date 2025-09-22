<?php

namespace App\Auth;

/**
 * Validates password strength against configurable rules.
 */
class PasswordValidator {
    private const MIN_LENGTH      = 8;                      /** Minimum allowed password length */
    private const REQUIRE_UPPER   = true;                   /** Require at least one uppercase letter */
    private const REQUIRE_LOWER   = true;                   /** Require at least one lowercase letter */
    private const REQUIRE_DIGIT   = true;                   /** Require at least one numeric digit */
    private const REQUIRE_SPECIAL = false;                  /** Require at least one special character */

    /**
     * Validate password against all requirements.
     */
    public function isValid(string $password): bool {
        if (strlen($password) < self::MIN_LENGTH) {
            return false;
        }

        $patterns = [
            self::REQUIRE_UPPER   => '/[A-Z]/',
            self::REQUIRE_LOWER   => '/[a-z]/',
            self::REQUIRE_DIGIT   => '/\d/',
            self::REQUIRE_SPECIAL => '/[\W_]/',
        ];

        foreach ($patterns as $requirement => $pattern) {
            if ($requirement && !preg_match($pattern, $password)) {
                return false;
            }
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