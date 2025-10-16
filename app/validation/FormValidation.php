<?php
namespace App\Validation;

class FormValidation {
	protected array $errors = [];
	protected array $cleanData = [];

	/**
	 * Sanitize an array of strings: trim, strip tags, drop empties, deduplicate.
	 */
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

	/**	Sanitize and filter input data, always keeps all expected keys, never drops them.
	 *      @param array $input
	 *      @return bool True if valid, false if errors found.
	 */
	public function sanitizeInput(array $input, string $mode = 'add'): bool {
		$this->errors = [];
		$this->cleanData = [];

		// Define expected fields and their sanitizers
		$expected = [
			'book_name'     => fn($v) => trim(strip_tags((string)$v)),
			'book_writers'  => fn($v) => $this->sanitizeArray($v),
			'book_genres'   => fn($v) => $this->sanitizeArray($v),
			'book_offices'  => fn($v) => $this->sanitizeArray($v),
		];

		// Only include book_id in edit mode
		if ($mode === 'edit') {
			$expected['book_id'] = function($v) {
				$id = filter_var($v, FILTER_VALIDATE_INT);
				if ($id === false && $v !== null) {
					$this->errors['book_id'] = 'Ongeldige boek-ID.';
				}
				return $id !== false && $id !== null ? $id : 0;
			};
		}

		// Apply sanitizers
		foreach ($expected as $key => $sanitizer) {
			$raw = $input[$key] ?? null;
			$this->cleanData[$key] = $sanitizer($raw);
		}

		return empty($this->errors);
	}

	/**	Validate book form data. Mode 'add' requires all fields, 'edit' only validates non-empty fields.
	 *		@param array $data
	 *		@param string $mode 'add' or 'edit'
	 *		@return bool True if valid, false if errors found.
	 */
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

		// Optionally: add more rules here

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