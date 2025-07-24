<?php
namespace App\Controllers;

use App\Core\Database\Database;
use Input\Request;

class BookController
{
    private PDO $pdo;
    private Request $request;

    public function __construct()
    {
        $this->pdo     = Database::get('database')->pdo();
        $this->request = Database::get('request');
    }

    public function extendBook(): void
    {
        $bookId = $this->request->getInt('book_id');
        $days   = $this->request->getInt('extend_days', 7);

        $stmt = $this->pdo->prepare("
            UPDATE book_stat
               SET periode_length = periode_length + :days
             WHERE book_id = :id
        ");
        $stmt->execute([
            ':days' => $days,
            ':id'   => $bookId
        ]);

        echo "Boek verlengd met {$days} dagen.";
    }
}