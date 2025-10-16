<?php
namespace App;

use App\Validation\{FormValidation, PasswordValidation};

class ValidationService {
    protected FormValidation $formValidator;
    protected PasswordValidation $passwordValidator;

    /*  Construct the validation service. */
    public function __construct() {
        $this->formValidator = new FormValidation();
        $this->passwordValidator = new PasswordValidation();

        App::getService('logger')->info(
            "Service 'val' has constructed 'FormValidation'",
            'validationservice'
        );
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
        return $this->formValidator->sanitizeInput($input);
    }

    /*  Validate book edit form data. */
    public function validateBookForm(array $data, string $mode): bool {
        return $this->formValidator->validateBookForm($data, $mode);
    }

    /*  Return the book form validation errors. */
    public function valErrors(): array {
        return $this->formValidator->errors();
    }

    /*  Return the sanitized book form data. */
    public function sanData(): array {
        return $this->formValidator->cleanData();
    }
}