<?php

namespace Ext\Controllers;

use App\App;

/* Handles general view rendering and redirects. */
class ViewController {
    /** Initial landing view, redirecting guests to the login view.
     *     @return void Redirects to login or home.
     */
    public function landing() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        if (!$userAgent) {
            App::getService('logger')->warning(
                'No user-agent found',
                'system'
            );

            return App::redirect('/login');
        }

        if (empty($_SESSION['user'])) {
            $_SESSION['user'] = [
                'role' => 'Guest'
            ];
        }

        if (!isset($_SESSION['user']['role'])) {
            App::getService('logger')->error(
                'Session missing user role',
                'system'
            );

            return App::redirect('/login');
        }

        // If user is a guest, redirect to login
        if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? 'Guest') === 'Guest') {
            return App::redirect('/login');
        }

        // Otherwise, show home/dashboard
        return $this->home();
    }

    /** Home/dashboard view for authenticated users.
     *    @return void Renders the home view with book data.
     */
    public function home() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] === 'Guest') {
            return App::redirect('/');
        }

        $books = App::getService('books')->getAllForDisplay();

        return App::view('main', ['books' => $books ?? null]);
    }
}