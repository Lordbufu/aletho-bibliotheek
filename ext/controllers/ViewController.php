<?php

namespace ext\controllers;

use App\App;

class ViewController {
    public function landing() {
        return App::view('main');
    }
}