<?php
namespace App;

use App\Validation\{FormValidation, PasswordValidation};

class ValidationService {
    protected FormValidation     $formValidator;
    protected PasswordValidation $passwordValidator;

    /*  Construct the validation service. */
    public function __construct() {
        $this->formValidator     = new FormValidation();
        $this->passwordValidator = new PasswordValidation();
    }

    /*  Get the FormValidation object. */
    public function formVal(): FormValidation {
        return $this->formValidator;
    }

    /*  Get the PasswordValidation object. */
    public function pwVal(): PasswordValidation {
        return $this->passwordValidator;
    }

    /*  Sanitize and filter input data. */
    public function sanitizeInput(array $input, string $mode): bool {
        return $this->formValidator->sanitizeInput($input, $mode);
    }

    /*  Validate book edit form data. */
    public function validateBookForm(array $data, string $mode): bool {
        return $this->formValidator->validateBookForm($data, $mode);
    }

    /*  Validate status change form data. */
    public function validateStatusChangeForm(array $data): bool {
        return $this->formValidator->validateStatusChangeForm($data);
    }

    /*  Return the book form validation errors. */
    public function errors(): array {
        return $this->formValidator->errors();
    }

    /*  Return the sanitized book form data. */
    public function cleanData(): array {
        return $this->formValidator->cleanData();
    }

    /*  Validate the user login form data. */
    public function validateUserLogin(array $data): bool {
        return $this->formValidator->validateUserLogin($data);
    }

    /*  Validate password change form data. */
    public function validatePasswordChange(array $data, bool $isGlobalAdmin = false): bool {
        return $this->formValidator->validatePasswordChange($data, $isGlobalAdmin);
    }

    /*  Validate status periode change data */
    public function validateStatusPeriod(array $data): bool {
        return $this->formValidator->validateStatusPeriod($data);
    }
}