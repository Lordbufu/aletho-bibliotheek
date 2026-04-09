<?php

namespace App\Validation;

/** Validates password strength against configurable rules. */
class PasswordValidation {
    private const MIN_LENGTH      = 8;
    private const REQUIRE_UPPER   = true;
    private const REQUIRE_LOWER   = true;
    private const REQUIRE_DIGIT   = true;
    private const REQUIRE_SPECIAL = false;

    /** Validate password against all requirements. */
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

    /** Return current password requirements for display. */
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