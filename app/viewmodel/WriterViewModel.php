<?php
namespace App\ViewModel;

final class WriterViewModel {
    public int      $id;    // PK for `writers`.`writer_id`.
    public string   $naam;  // Derived from `writers`.`writer_name`.

    public function __construct(array $data) {
        $this->id   = $data['writer_id'];
        $this->naam = $data['writer_name'];
    }

    public static function formatOne(array $schrijver): self {
        return new self($schrijver);
    }

    public static function formatMany(array $schrijvers): array {
        $formatted = [];

        foreach ($schrijvers as $schrijver) {
            $format = [
                'writer_id'     => $schrijver->writer_id,
                'writer_name'   => $schrijver->writer_name
            ];

            $formatted[] = new self($format);
        }

        return $formatted;
    }
}