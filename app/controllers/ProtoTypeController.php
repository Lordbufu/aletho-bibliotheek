<?php

namespace App\Controllers;

use App\Core\App;

class ProtoTypeController {
    public function home() {
        $user = [ 'userType' => 'admin' ]; // Example user data, replace with actual logic
        return App::view('main.view', $user);
    }

    public function test() {
        return 'This is a test route.';
    }
}