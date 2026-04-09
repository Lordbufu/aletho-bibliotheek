<?php

namespace Ext\Controllers\Formatter;

use App\Libs\Context\BookContext;

final class BookFormatter {
    public function format(BookContext $book): array {
        $due = $book->status['dueDate'] ?? null;

        return [
            'id'            => $book->id,
            'title'         => $book->title,
            'writers'       => implode(', ', $book->writers),
            'genres'        => implode(', ', $book->genres),
            'office'        => $book->homeOfficeName,
            'curOffice'     => $book->curOfficeName,
            'status'        => $book->status['type'] ?? 'Onbekend',
            'is_reserved'   => $book->resvLoanerId !== null,
            'dueDate'       => $due?->format('Y-m-d') ?? 'Onbekend',
            'loanerHistory' => $book->status['loanerHistory'] ?? []
        ];
    }

    public function formatMany(array $books): array {
        $out = [];
        foreach ($books as $book) {
            $out[] = $this->format($book);
        }
        return $out;
    }
}
