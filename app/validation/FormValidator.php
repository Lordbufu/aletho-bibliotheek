<?php
/** @return: structure for all functions:
 *  [
 *      'valid' => bool,
 *      'data'  => normalizedData,
 *      'errors' => [field => message]
 *  ]
 */

namespace App\Validation;

use App\Libs\Types\StatusType;

class FormValidator {
    // Re-factor status: tested and working
    /** Helper: To merge ids and names from the input, so the service layer can process it correctly */
    private function mergeNamesAndIds(?array $names, ?array $ids): array {
        $names = $names ?? [];
        $ids   = $ids ?? [];

        $clean = [];

        foreach ($names as $index => $name) {
            $clean[] = [
                'id'   => isset($ids[$index]) ? $this->cleanInt($ids[$index]) : null,
                'name' => $this->cleanString($name)
            ];
        }

        return $clean;
    }

    // Re-factor status: No changes
    /** Helper: Trim and normalize strings */
    private function cleanString(?string $value): ?string {
        if ($value === "") {
            return null;
        }

        $value = trim((string)$value);

        return $value === '' ? null : $value;
    }

    // Re-factor status: tested and working
    /** Helper: Normalize the login name/email  */
    private function normalizeIdentifier(?string $value): ?string {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        // Email?
        if (str_contains($value, '@')) {
            return strtolower($value);
        }

        // Username normalization
        $value = strtolower($value);
        $value = preg_replace('/\s+/', ' ', $value); // collapse spaces

        // Optional: remove accents
        $value = iconv('UTF-8', 'ASCII//TRANSLIT', $value);

        return $value;
    }

    // Re-factor status: No changes
    /** Helper: Validate email inputs */
    private function cleanEmail(?string $value): ?string {
        $value = $this->cleanString($value);
        return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : null;
    }

    // Re-factor status: No changes
    /** Helper: Validate integere inputs */
    private function cleanInt(int $value): ?int {
        return filter_var($value, FILTER_VALIDATE_INT) !== false
            ? (int)$value
            : null;
    }

    // Re-factor status: tested and working
    /** API: Validate the login form */
    public function validateLogin(array $input): array {
        $errors = [];
        $clean  = [];

        // Normalize identifier
        $clean['identifier'] = $this->normalizeIdentifier($input['userName'] ?? null);

        // Password: only trim
        $clean['password'] = trim((string)($input['userPw'] ?? ''));

        // Validate
        if (!$clean['identifier']) {
            $errors['userName'] = 'Gebruikersnaam of e-mail is verplicht.';
        }

        if ($clean['password'] === '') {
            $errors['password'] = 'Wachtwoord is verplicht.';
        }

        return [
            'valid'  => empty($errors),
            'data'   => $clean,
            'errors' => $errors
        ];
    }

    // Re-factor status: No changes
    /** API: Password change form for office_admins */
    public function validatePasswordChange(array $input): array {
        $errors = [];
        $clean  = [];

        $clean['current_password']  = $this->cleanString($input['current_password'] ?? null);
        $clean['new_password']      = $this->cleanString($input['new_password'] ?? null);

        if (!$clean['current_password']) {
            $errors['current_password'] = 'Huidig wachtwoord is verplicht.';
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

    // Re-factor status: No changes
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

    // Re-factor status: tested and working
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

        if ($mode === 'add' || array_key_exists('book_schrijvers', $input)) {
            $clean['writers'] = $this->mergeNamesAndIds(
                $input['book_schrijvers'] ?? null,
                $input['book_schrijvers_ids'] ?? null
            );

            if (empty($clean['writers'])) {
                $errors['writers'] = 'Minimaal één schrijver is verplicht.';
            }
        }

        if ($mode === 'add' || array_key_exists('book_genres', $input)) {
            $clean['genres'] = $this->mergeNamesAndIds(
                $input['book_genres'] ?? null,
                $input['book_genres_ids'] ?? null
            );

            if (empty($clean['genres'])) {
                $errors['genres'] = 'Minimaal één genre is verplicht.';
            }
        }

        if ($mode === 'add' || array_key_exists('book_locatie', $input)) {
            $clean['office'] = [
                'id'    => $input['book_locatie'][0] ?? null
            ];

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

    // Re-factor status: tested and working
    /** API: Validate status edit form data */
    public function validateStatusPeriod(array $input): array {
        $errors = [];
        $clean  = [];

        $clean['status_id'] = (int)($input['status_id'] ?? 0);
        if ($clean['status_id'] <= 0) {
            $errors['status_id'] = 'Ongeldige status geselecteerd.';
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

        if ($clean['reminder_day'] >= $clean['period_length']) {
            $errors['reminder_day'] = 'Herinneringsdag moet vóór het einde van de periode liggen.';
        }

        if ($clean['overdue_day'] > $clean['period_length']) {
            $errors['overdue_day'] = 'Te-laat dag kan niet groter zijn dan de periode.';
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

        $clean['status_id']             = $this->cleanInt($input['status_id'] ?? null);
        $clean['book_id']               = $this->cleanInt($input['book_id'] ?? null);

        if (!$clean['book_id']) {
            $errors['book_id']          = 'Geen boek gevonden om de status van te veranderen.';
        }

        if (!$clean['status_id']) {
            $errors['status_id']        = 'Status is verplicht.';
        }

        // TODO: Remove overdatum check when cron jobs are finalized, this is for testing purposes only
        if ($clean['status_id'] === StatusType::toId('Aanwezig') || $clean['status_id'] === StatusType::toId('Overdatum')) {
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