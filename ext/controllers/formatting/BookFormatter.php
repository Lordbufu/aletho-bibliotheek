<?php
namespace Ext\Controllers\Formatting;

use App\App;

class BookFormatter {
    public function format(array $book): array {
        $libs = App::getLibraries();

        return [
            'id'        => $book['id'],
            'title'     => $book['title'],
            'writers'   => $libs->writers()->getWriterNamesByBookId($book['id']),
            'genres'    => $libs->genres()->getGenreNamesByBookId($book['id']),
            'office'    => $libs->offices()->getOfficeNameByOfficeId($book['home_office']),
            'curOffice' => $libs->offices()->getOfficeNameByOfficeId($book['cur_office']),
            'status'    => $libs->statuses()->getBookStatus($book['id']),
            'dueDate'   => $libs->statuses()->getBookDueDate($book['id']),
            'curLoaner' => $libs->loaners()->getLoanersByBookId($book['id'], 'current', 'Geen huidige lener', 1, true),
            'prevLoaners' => $libs->loaners()->getLoanersByBookId($book['id'], 'previous', 'Geen vorige leners', 5, true),
            'canEditOffice' => App::getService('auth')->canManageOffice($book['home_office']),
        ];
    }

    public function formatMany(array $books): array {
        $out = [];
        foreach ($books as $book) {
            if ($book['active']) {
                $out[] = $this->format($book);
            }
        }
        return $out;
    }
}