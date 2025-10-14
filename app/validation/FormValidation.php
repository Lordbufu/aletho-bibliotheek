<?php
namespace App\Validation;

class FormValidation {
	protected array $errors = [];
	protected array $cleanData = [];

	/**	Sanitize and filter input data, always keeps all expected keys, never drops them.
	 *      @param array $input
	 *      @return bool True if valid, false if errors found.
	 */
	public function sanitizeInput(array $input): bool {
		$this->errors = [];
		$this->cleanData = [];

		// Always expect these fields
		$expected = [
			'book_id', 'book_name', 'book_writers', 'book_genres', 'book_offices'
		];

		// Set safe defaults
		foreach ($expected as $key) {
			if (!isset($input[$key])) {
				if ($key === 'book_id') {
					$this->cleanData[$key] = 0;
				} elseif ($key === 'book_name') {
					$this->cleanData[$key] = '';
				} else {
					$this->cleanData[$key] = [];
				}
			}
		}

		// Sanitize each field
		// book_id
		$id = isset($input['book_id']) ? filter_var($input['book_id'], FILTER_VALIDATE_INT) : 0;
		$this->cleanData['book_id'] = $id !== false && $id !== null ? $id : 0;
		if ($id === false && isset($input['book_id'])) {
			$this->errors['book_id'] = 'Ongeldige boek-ID.';
		}

		// book_name
		$name = isset($input['book_name']) ? trim(strip_tags($input['book_name'])) : '';
		$this->cleanData['book_name'] = $name;

		// book_writers, book_genres, book_offices
		foreach (['book_writers', 'book_genres', 'book_offices'] as $arrKey) {
			$arr = isset($input[$arrKey]) && is_array($input[$arrKey]) ? $input[$arrKey] : [];
			$cleaned = array_map(fn($v) => is_string($v) ? trim(strip_tags($v)) : '', $arr);
			$cleaned = array_filter($cleaned, fn($v) => $v !== '');
			$this->cleanData[$arrKey] = array_values($cleaned);
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
				$this->errors['book_title'] = 'Titel is verplicht.';
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