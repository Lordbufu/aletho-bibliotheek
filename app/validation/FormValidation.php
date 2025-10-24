<?php
namespace App\Validation;

class FormValidation {
	protected array $errors = [];
	protected array $cleanData = [];

	/*	Sanitize an array of strings: trim, strip tags, drop empties, deduplicate. */
	protected function sanitizeArray($value): array {
		if (!is_array($value)) {
			return [];
		}

		$cleaned = array_map(
			fn($v) => is_string($v) ? trim(strip_tags($v)) : '',
			$value
		);

		$cleaned = array_filter($cleaned, fn($v) => $v !== '');
		$cleaned = array_unique($cleaned);

		return array_values($cleaned);
	}

	/*	Sanitize and filter input data, always keeps all expected keys, never drops them. */
	public function sanitizeInput(array $input, string $mode = 'add'): bool {
		$this->errors = [];
		$this->cleanData = [];

		$expected = [
			'book_name'     => fn($v) => trim(strip_tags((string)$v)),
			'book_writers'  => fn($v) => $this->sanitizeArray($v),
			'book_genres'   => fn($v) => $this->sanitizeArray($v),
			'book_offices'  => fn($v) => $this->sanitizeArray($v),
		];

		if ($mode === 'edit') {
			$expected['book_id'] = function($v) {
				$id = filter_var($v, FILTER_VALIDATE_INT);

				if ($id === false || $id === null) {
					$this->errors['book_id'] = 'Geen geldige book data ontvangen !';
					return 0;
				}

				return $id;
			};
		}

		foreach ($expected as $key => $sanitizer) {
			$raw = $input[$key] ?? null;
			$this->cleanData[$key] = $sanitizer($raw);
		}

		return empty($this->errors);
	}

	/*	Validate the user login form data, and store potential errors. */
	public function validateUserLogin(array $data): bool {
		$this->errors = [];
		$this->cleanData = [];

		$username = trim(strip_tags((string)($data['userName'] ?? '')));
		$password = (string)($data['userPw'] ?? '');

		if ($username === '') {
			$this->errors['userName'] = 'Gebruikersnaam is verplicht.';
		}
		if ($password === '') {
			$this->errors['userPw'] = 'Wachtwoord is verplicht.';
		}

		$this->cleanData = [
			'userName' => $username,
			'userPw'   => $password,
		];

		return empty($this->errors);
	}

	/*	Validate the password change form data, and store potential errors. */
	public function validatePasswordChange(array $data, bool $isGlobalAdmin = false): bool {
		$this->errors = [];
		$this->cleanData = [];

		// Sanitize
		$userName        = trim(strip_tags((string)($data['user_name'] ?? '')));
		$currentPassword = (string)($data['current_password'] ?? '');
		$newPassword     = (string)($data['new_password'] ?? '');
		$confirmPassword = (string)($data['confirm_password'] ?? '');

		// Global admin requires a target username
		if ($isGlobalAdmin) {
			if ($userName === '') {
				$this->errors['user_name'] = 'Gebruikersnaam is verplicht.';
			}
		} else {
			if ($currentPassword === '') {
				$this->errors['current_password'] = 'Huidig wachtwoord is verplicht.';
			}
		}

		if ($newPassword === '') {
			$this->errors['new_password'] = 'Nieuw wachtwoord is verplicht.';
		}

		if ($confirmPassword === '') {
			$this->errors['confirm_password'] = 'Bevestig nieuw wachtwoord is verplicht.';
		}

		if ($newPassword !== '' && $confirmPassword !== '' && $newPassword !== $confirmPassword) {
			$this->errors['confirm_password'] = 'Wachtwoorden komen niet overeen.';
		}

		$this->cleanData = [
			'user_name'        => $userName,
			'current_password' => $currentPassword,
			'new_password'     => $newPassword,
			'confirm_password' => $confirmPassword,
		];

		return empty($this->errors);
	}

	/*	Validate book form data. Mode 'add' requires all fields, 'edit' only validates non-empty fields. */
	public function validateBookForm(array $data, string $mode = 'add'): bool {
		$this->errors = [];

		// Title
		if ($mode === 'add' || ($mode === 'edit' && $data['book_name'] !== '')) {
			if ($data['book_name'] === '') {
				$this->errors['book_name'] = 'Titel is verplicht.';
			}
		}

		// Writers
		if ($mode === 'add' || ($mode === 'edit' && !empty($data['book_writers']))) {
			if (empty($data['book_writers']) || !is_array($data['book_writers'])) {
				$this->errors['book_writers'] = 'Minimaal één schrijver is verplicht.';
			}
		}

		// Genres
		if ($mode === 'add' || ($mode === 'edit' && !empty($data['book_genres']))) {
			if (empty($data['book_genres']) || !is_array($data['book_genres'])) {
				$this->errors['book_genres'] = 'Minimaal één genre is verplicht.';
			}
		}

		// Offices
		if ($mode === 'add' || ($mode === 'edit' && !empty($data['book_offices']))) {
			if (empty($data['book_offices']) || !is_array($data['book_offices'])) {
				$this->errors['book_offices'] = 'Minimaal één locatie is verplicht.';
			}
		}

		return empty($this->errors);
	}

	/* Simple get errors helper. */
	public function errors(): array {
		return $this->errors;
	}

	/* Simple get get data helper. */
	public function cleanData(): array {
		return $this->cleanData;
	}
}