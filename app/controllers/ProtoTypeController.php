<?php

namespace App\Controllers;

use App\Core\App;

class ProtoTypeController {
    public function home() {
        //unset($_SESSION['user']);
        // Init user test data
        //App::get('auth')->check();
        //App::get('auth')->login('test', 'test');

        // Testing status expire data display/formating
        $dateObj = new \DateTime('25-01-1873');
        $bookStatusExp = $dateObj->format('Y-m-d');
        //$error = "Uw inlog gegevens zijn niet correct, probeer het nogmaals!";
        $error ='';


        $books = [['id' => 1, 'name' => 'Book 1', 'author' => 'Author 1', 'genre' => 'Roman', 'office_id' => 1, 'status_id' => 1], ['id' => 2, 'name' => 'Book 2', 'author' => 'Author 2', 'genre' => 'Non-fictie', 'office_id' => 2, 'status_id' => 3], ['id' => 3, 'name' => 'Book 3', 'author' => 'Author 1', 'genre' => 'Science Fiction', 'office_id' => 1, 'status_id' => 2]];
        $statuses = [['id' => 1, 'type' => 'Uitgeleend', 'periode_length' => 14, 'reminder_day' => 7, 'overdue_day' => 15], ['id' => 2, 'type' => 'Beschikbaar', 'periode_length' => 38, 'reminder_day' => 30, 'overdue_day' => 39], ['id' => 3, 'type' => 'Gereserveerd', 'periode_length' => 7, 'reminder_day' => 5, 'overdue_day' => 8], ['id' => 4,'type' => 'Verloren', 'periode_length' => 27, 'reminder_day' => 20, 'overdue_day' => 28]];
        $genres = [['id' => 1, 'name' => 'Roman'], ['id' => 2, 'name' => 'Non-fictie'], ['id' => 3, 'name' => 'Science Fiction']];
        $offices = [['id' => 1, 'name' => 'Hoofdkantoor'], ['id' => 2, 'name' => 'Filiaal Noord']];
        $loanerHistory = [['id' => 1, 'name' => 'Alice'], ['id' => 2, 'name' => 'Bob'], ['id' => 3, 'name' => 'Charlie']];

        $data = [
            'statusExp' => $bookStatusExp,
            'books' => $books,
            'statuses' => $statuses,
            'genres' => $genres,
            'offices' => $offices,
            'loanerHistory' => $loanerHistory,
            'error' => $error
        ];

        //debugData($_SESSION);

        return App::view('main.view', $data);
    }
}