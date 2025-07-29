<?php

namespace App\Controllers;

use App\Core\App;

class ProtoTypeController {
    public function home() {
        $data = [
            'userType' => 'office_admins', // Can be user, office_admins, or global_admins
            'currentOfficeId' => 1,
            //'userType' => 'global_admins', // Can be user, office_admins, or global_admins
            //'userType' => 'user', // Can be user, office_admins, or global_admins
            'books' => [
                [   'id' => 1,
                    'name' => 'Book 1',
                    'author' => 'Author 1',
                    'genre' => 'Roman',
                    'office' => 'Hoofdkantoor',
                    'office_id' => 1
                ],
                [   'id' => 2,
                    'name' => 'Book 2',
                    'author' => 'Author 2',
                    'genre' => 'Non-fictie',
                    'office' => 'Filiaal Noord',
                    'office_id' => 2
                ]
            ],
            'statuses' => [
                [   'id' => 1,
                    'type' => 'Status 1',
                    'periode_length' => 30,
                    'reminder_day' => 5,
                    'overdue_day' => 2 ],
                [   'id' => 2,
                    'type' => 'Status 2',
                    'periode_length' => 14,
                    'reminder_day' => 3,
                    'overdue_day' => 1 ],
                [   'id' => 3,
                    'type' => 'Status 3',
                    'periode_length' => 60,
                    'reminder_day' => 10,
                    'overdue_day' => 5 ]
            ],
            'statusTypes' => [
                [ 'id' => 1, 'type' => 'Uitgeleend' ],
                [ 'id' => 2, 'type' => 'Beschikbaar' ],
                [ 'id' => 3, 'type' => 'Gereserveerd' ],
                [ 'id' => 4, 'type' => 'Verloren' ]
            ],
            'users' => [
                ['id' => 1, 'name' => 'Alice'],
                ['id' => 2, 'name' => 'Bob'],
                ['id' => 3, 'name' => 'Charlie']
            ],
            'genres' => [
                ['id' => 1, 'name' => 'Roman'],
                ['id' => 2, 'name' => 'Non-fictie'],
                ['id' => 3, 'name' => 'Science Fiction']
            ],
            'offices' => [
                ['id' => 1, 'name' => 'Hoofdkantoor'],
                ['id' => 2, 'name' => 'Filiaal Noord']
            ]
        ];

        return App::view('main.view', $data);
    }
}