<?php
namespace App\ViewModel;

// Re-factor status: tested and working
final class BookViewModel {
    public int      $id;            // PK for `books`.`book_id`
    public string   $title;         // Derived from `books`.`book_title`
    public array    $locatie;       // Derived from `books`.`book_cur_loc` -> `locations`.`location.id`
    public ?string  $schrijvers;    // Derived from `book_writers`.`writer_id` -> `writers`.`writer_id`
    public ?string  $genres;        // Derived from `book_genres`.`genre_id` -> `genres`.`genre_id`
    public ?array   $loaners;       // Derived from `book_loaners`.`loaner_id` -> `loaners`.`loaner_name`
    public string   $status;        // Derived from `status`.`status_name`
    public ?string  $statusEinde;   // Derived from `book_loaners`.`end_at`
    public bool     $gereserveerd;  // Derived from `book_reservation` entries

    public function __construct(array $data) {
        $this->id           = $data['book_id'];
        $this->title        = $data['book_title'];
        $this->locatie      = $data['cur_loc'];
        $this->schrijvers   = implode(', ', array_map( fn($w) => $w['writer_name'], $data['writers'] ));
        $this->genres       = implode(', ', array_map( fn($g) => $g['genre_name'], $data['genres'] ));
        $this->loaners      = $data['loanerHistory'] ?? [];
        $this->status       = $data['status_name'] ?? 'Onbekend';
        $this->statusEinde  = $data['dueDate']?->format('Y-m-d') ?? null;
        $this->gereserveerd = (bool) $data['isReserved'];
    }

    public static function formatOne(array $book): self {
        return new self($book);
    }

    public static function formatMany(array $boeken, array $schrijvers, array $genres, array $locaties, array $status, array $leners, array $reseveringen): array {
        $formatted = [];

        foreach ($boeken as $boek) {
            $currentLoaner = $leners[$boek->book_id];
            $format = [
                'book_id'       => $boek->book_id,
                'book_title'    => $boek->book_title,
                'writers'       => $schrijvers[$boek->book_id],
                'genres'        => $genres[$boek->book_id],
                'cur_loc'       => [ 'naam' => $locaties[$boek->book_id]->loc_name, 'id' => $locaties[$boek->book_id]->loc_id ],
                'status_name'   => $status[$boek->book_id]->status_name,
                'dueDate'       => $currentLoaner[$boek->book_id]->end_at ?? null,
                'loanerHistory' => array_map(fn($l) => $l->loaner_name, $leners[$boek->book_id]),
                'isReserved'    => $reseveringen[$boek->book_id]
            ];

            $formatted[] = new self($format);
        }

        return $formatted;
    }
}