<?php
namespace Ext\Controllers;

use App\App;

/*  Handles general view rendering and redirects. */
class ViewController {
    /*  Initial landing view, redirecting guests to the login view. */
    public function landing() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        if (!$userAgent) {
            App::getService('logger')->warning('No user-agent found', 'ViewController');
            return App::redirect('/login');
        }

        if (empty($_SESSION['user'])) {
            $_SESSION['user'] = ['role' => 'Guest'];
        }

        if (!isset($_SESSION['user']['role'])) {
            App::getService('logger')->warning('Session missing user role', 'ViewController');
            return App::redirect('/login');
        }

        if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? 'Guest') === 'Guest') {
            return App::redirect('/login');
        }

        return $this->home();
    }

    /*  Home/dashboard view for authenticated users. */
    public function home() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] === 'Guest') {
            return App::redirect('/');
        }

        // dd(App::getService('books')->getAllForDisplay());

        return App::view('main', [
            'books' => App::getService('books')->getAllForDisplay() ?? null,
            'canEdit' => App::getService('auth')->can('manageBooks')
        ]);
    }
}