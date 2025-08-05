<?php

namespace App\Controllers;

use App\Core\App;
// use DateTime;

class ProtoTypeController {
    public function home() {
        // $user = [ 'name' => 'user', 'is_loaner' => 1, 'is_office_admin' => 0, 'is_global_admin' => 0 ];
        // $user = [ 'name' => 'officeAdmin', 'is_loaner' => 0, 'is_office_admin' => 1, 'is_global_admin' => 0, 'office_id' => 1 ];
        $user = [ 'name' => 'globaleAdmin', 'is_loaner' => 0, 'is_office_admin' => 0, 'is_global_admin' => 1 ];

        // Testing status expire data display/formating
        $dateObj = new \DateTime('25-01-1873');
        $statusExp = $dateObj->format('Y-m-d');

        $data = [
            'user' => [ 'name'=> $user['name'] ],
            'perm' => App::get('perm')->setUser($user),
            'statusExp' => $statusExp,
            'books' => [
                [ 'id' => 1, 'name' => 'Book 1', 'author' => 'Author 1', 'genre' => 'Roman', 'office_id' => 1, 'status_id' => 1 ],
                [ 'id' => 2, 'name' => 'Book 2', 'author' => 'Author 2', 'genre' => 'Non-fictie', 'office_id' => 2, 'status_id' => 3 ],
                [ 'id' => 3, 'name' => 'Book 3', 'author' => 'Author 1', 'genre' => 'Science Fiction', 'office_id' => 1, 'status_id' => 2 ],
            ],
            'statuses' => [
                [ 'id' => 1, 'type' => 'Uitgeleend' ],
                [ 'id' => 2, 'type' => 'Beschikbaar' ],
                [ 'id' => 3, 'type' => 'Gereserveerd' ],
                [ 'id' => 4, 'type' => 'Verloren' ]
            ],
            'genres' => [
                ['id' => 1, 'name' => 'Roman'],
                ['id' => 2, 'name' => 'Non-fictie'],
                ['id' => 3, 'name' => 'Science Fiction']
            ],
            'offices' => [
                ['id' => 1, 'name' => 'Hoofdkantoor'],
                ['id' => 2, 'name' => 'Filiaal Noord']
            ],
            'loanerHistory' => [
                ['id' => 1, 'name' => 'Alice'],
                ['id' => 2, 'name' => 'Bob'],
                ['id' => 3, 'name' => 'Charlie']
            ]
        ];

        return App::view('main.view', $data);
    }
}