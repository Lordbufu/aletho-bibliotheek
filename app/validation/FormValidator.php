<?php
/** Return structure for all functions:
 *  [
 *      'valid' => bool,
 *      'data'  => normalizedData,
 *      'errors' => [field => message]
 *  ]
 */

namespace App\Validation;

use App\Libs\Types\StatusType;

class FormValidator {
    /** Helper: Trim and normalize strings */
    private function cleanString(?string $value): ?string {
        if ($value === "") {
            return null;
        }
        $value = trim((string)$value);
        return $value === '' ? null : $value;
    }

    /** Helper: Normalize array data for  writer(s)/genre(s)/office(s) */
    private function cleanList(?array $values): array {
        if (!$values) {
            return [];
        }

        $clean = array_map(
            fn($v) => is_string($v) ? trim($v) : '',
            $values
        );

        $clean = array_filter($clean, fn($v) => $v !== '');
        $clean = array_unique($clean);

        return array_values($clean);
    }

    /** Helper: Validate email inputs */
    private function cleanEmail(?string $value): ?string {
        $value = $this->cleanString($value);
        return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : null;
    }

    /** Helper: Validate integere inputs */
    private function cleanInt($value): ?int {
        return filter_var($value, FILTER_VALIDATE_INT) !== false
            ? (int)$value
            : null;
    }

    /** API: Validate the login form */
    public function validateLogin(array $input): array {
        $errors = [];
        $clean  = [];

        // Normalize
        $clean['userName'] = $this->cleanString($input['userName'] ?? null);
        $clean['password'] = $this->cleanString($input['userPw'] ?? null);

        // Validate
        if (!$clean['userName']) {
            $errors['userName'] = 'Gebruikersnaam is verplicht.';
        }

        if (!$clean['password']) {
            $errors['password'] = 'Wachtwoord is verplicht.';
        }

        return [
            'valid'  => empty($errors),
            'data'   => $clean,
            'errors' => $errors
        ];
    }

    /** API: Password change form for office_admins */
    public function validatePasswordChange(array $input): array {
        $errors = [];
        $clean  = [];

        $clean['old_password'] = $this->cleanString($input['current_password'] ?? null);
        $clean['new_password'] = $this->cleanString($input['new_password'] ?? null);

        if (!$clean['old_password']) {
            $errors['old_password'] = 'Huidig wachtwoord is verplicht.';
        }

        if (!$clean['new_password']) {
            $errors['new_password'] = 'Nieuw wachtwoord is verplicht.';
        }

        return [
            'valid'  => empty($errors),
            'data'   => $clean,
            'errors' => $errors
        ];
    }

    /** API: Password reset form for global_admins */
    public function validatePasswordReset(array $input): array {
        $errors = [];
        $clean  = [];

        $clean['user_name']  = $this->cleanString($input['user_name'] ?? null);
        $clean['new_password'] = $this->cleanString($input['new_password'] ?? null);

        if (!$clean['user_name']) {
            $errors['user_name'] = 'Gebruikersnaam of e-mail is verplicht.';
        }

        if (!$clean['new_password']) {
            $errors['new_password'] = 'Nieuw wachtwoord is verplicht.';
        }

        return [
            'valid'  => empty($errors),
            'data'   => $clean,
            'errors' => $errors
        ];
    }

    /** API: Validate all books forms based on a '$mode' switch */
    public function validateBookForm(array $input, string $mode = 'add'): array {
        $errors = [];
        $clean  = [];

        if ($mode === 'add' || array_key_exists('book_name', $input)) {
            $clean['title'] = $this->cleanString($input['book_name'] ?? null);
            if (!$clean['title']) {
                $errors['title'] = 'Titel mag niet leeg zijn.';
            }
        }

        if ($mode === 'add' || array_key_exists('book_writers', $input)) {
            $clean['writers'] = $this->cleanList($input['book_writers'] ?? null);
            if (empty($clean['writers'])) {
                $errors['writers'] = 'Minimaal één schrijver is verplicht.';
            }
        }

        if ($mode === 'add' || array_key_exists('book_genres', $input)) {
            $clean['genres'] = $this->cleanList($input['book_genres'] ?? null);
            if (empty($clean['genres'])) {
                $errors['genres'] = 'Minimaal één genre is verplicht.';
            }
        }

        if ($mode === 'add' || array_key_exists('book_offices', $input)) {
            $clean['office'] = $this->cleanList($input['book_offices'] ?? null);
            if (empty($clean['office'])) {
                $errors['office'] = 'Kantoor selectie is ongeldig.';
            }
        }

        return [
            'valid'  => empty($errors),
            'data'   => $clean,
            'errors' => $errors
        ];
    }

    /** API: Validate status edit form data */
    public function validateStatusPeriod(array $input): array {
        $errors = [];
        $clean  = [];

        $clean['status_type'] = (int)($input['status_type'] ?? 0);
        if ($clean['status_type'] <= 0) {
            $errors['status_type'] = 'Ongeldige status geselecteerd.';
        }

        $clean['period_length'] = $this->cleanInt($input['period_length'] ?? null);
        if ($clean['period_length'] === null || $clean['period_length'] < 7) {
            $errors['period_length'] = 'Periode moet minimaal 7 dagen zijn.';
        }

        $clean['reminder_day'] = $this->cleanInt($input['reminder_day'] ?? null);
        if ($clean['reminder_day'] === null || $clean['reminder_day'] < 2) {
            $errors['reminder_day'] = 'Herinneringsdag moet minimaal 2 dagen zijn.';
        }

        $clean['overdue_day'] = $this->cleanInt($input['overdue_day'] ?? null);
        if ($clean['overdue_day'] === null || $clean['overdue_day'] < 1) {
            $errors['overdue_day'] = 'Te-laat dag moet minimaal 1 dag zijn.';
        }

        return [
            'valid'  => empty($errors),
            'data'   => $clean,
            'errors' => $errors
        ];
    }

    /** Validate status change form data */
    public function validateStatusChange(array $input): array {
        $errors                         = [];
        $clean                          = [];

        $clean['status_type']           = $this->cleanInt($input['status_type'] ?? null);
        $clean['book_id']               = $this->cleanInt($input['book_id'] ?? null);

        if (!$clean['book_id']) {
            $errors['book_id']          = 'Geen boek gevonden om de status van te veranderen.';
        }

        if (!$clean['status_type']) {
            $errors['status_type']      = 'Status is verplicht.';
        }

        // TODO: Remove overdatum check when cron jobs are finalized, this is for testing purposes only
        if ($clean['status_type'] === StatusType::toId('Aanwezig') || $clean['status_type'] === StatusType::toId('Overdatum')) {
            return [
                'valid'  => empty($errors),
                'data'   => $clean,
                'errors' => $errors
            ];
        }

        $clean['loaner_name']           = $this->cleanString($input['loaner_name'] ?? null);
        $clean['loaner_email']          = $this->cleanEmail($input['loaner_email'] ?? null);
        $clean['loaner_location']       = $this->cleanString($input['loaner_location'] ?? null);
    
        if (!$clean['loaner_name']) {
            $errors['loaner_name']      = 'Naam is verplicht.';
        }

        if (!$clean['loaner_email']) {
            $errors['loaner_email']     = 'E-mail is ongeldig.';
        }

        if (!$clean['loaner_location']) {
            $errors['loaner_location']  = 'Locatie is verplicht.';
        }

        return [
            'valid'  => empty($errors),
            'data'   => $clean,
            'errors' => $errors
        ];
    }
}