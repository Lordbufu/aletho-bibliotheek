<?php
namespace App\Libs;

use App\Libs\Context\ReservationContext;

final class ReservationRepo {
    private \App\Database $db;

    public function __construct() {
        $this->db = \App\App::getService('database');
    }  

    /** API: Check if there are any reservations for this/these id(s) */
    public function checkReservationsForBookId(mixed $book_ids): ?array {
        if (!$book_ids) {
            return [];
        }

        $book_ids = is_array($book_ids) ? $book_ids : [$book_ids];
        $book_ids = array_map('intval', $book_ids);

        $placeholders = [];
        $params = [];
        $out = [];

        foreach ($book_ids as $i => $id) {
            $key = "book_id{$i}";
            $placeholders[] = ":{$key}";
            $params[$key] = $id;
        }

        $sql = "
            SELECT book_id
            FROM book_reservations
            WHERE book_id IN (" . implode(',', $placeholders) . ")
        ";

        $rows = $this->db->query()->fetchAll($sql, $params);

        $out = array_fill_keys($book_ids, false);

        foreach ($rows as $r) {
            $bid = (int)$r['book_id'];
            $out[$bid] = true;
        }

        return $out;
    }

    /** API: Request ACTIVE reservation context for just this book_id */
    public function getReservationByBookId(int $book_id): ?ReservationContext {
        $row = $this->db->query()->fetchOne("SELECT * FROM book_reservations WHERE book_id = :bookId AND is_active = 1",
            ["bookId" => $book_id]
        );

        return $row ? new ReservationContext($row) : null;
    }
}