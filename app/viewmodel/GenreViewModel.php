<?php
namespace App\ViewModel;

final class GenreViewModel {
    public int      $id;    // PK for `genres`.`genre_id`.
    public string   $naam;  // Derived from `genres`.`genre_name`.

    public function __construct(array $data) {
        $this->id   = $data['genre_id'];
        $this->naam = $data['genre_name'];
    }

    public static function formatOne(array $genre): self {
        return new self($genre);
    }

    public static function formatMany(array $genres): array {
        $formatted = [];

        foreach ($genres as $genre) {
            $format = [
                'genre_id'     => $genre->genre_id,
                'genre_name'   => $genre->genre_name
            ];

            $formatted[] = new self($format);
        }

        return $formatted;
    }
}