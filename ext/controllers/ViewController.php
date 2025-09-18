<?php

namespace ext\controllers;

use App\App;

class ViewController {
    /**
     * Initial landing view, redirecting guests to the login view.
     */
    public function landing() {
        if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'Guest') {
            return App::redirect('/login');
            dd('Guest user needs a login route !!');
        } elseif (isset($_SESSION['user'])) {
            return App::redirect('/home');
        } else {
            exit;
        }
    }

    /**
     * The initial landing, after the login process.
     */
    public function home() {
        if(!isset($_SESSION['user'])) {
            return App::redirect('/');
        }

        $books = App::getService('books')->getAllForDisplay();

        return App::view('main', ['books' => $books]);
    }
}