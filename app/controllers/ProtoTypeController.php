<?php

namespace App\Controllers;

use App\Core\App;

class ProtoTypeController {
    public function home() {
        $data = [
            'userType' => 'admin',                              // Example user type, can be 'admin', 'user', etc; Uncomment to get the login view.
            'books' => [                                        // Example book data
                ['id' => 1, 'name' => 'Book 1', 'author' => 'Author 1'],
                ['id' => 2, 'name' => 'Book 2', 'author' => 'Author 2'],
                // Add more books as needed
            ]
        ];
        
        return App::view('main.view', $data);
    }

    public function test() {
        return 'This is a test route.';
    }
}