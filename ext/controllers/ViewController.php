<?php
namespace Ext\Controllers;

use App\App;
use Ext\Controllers\Formatting\BookFormatter;
use Ext\Controllers\Middleware\AuthMiddleware;

class ViewController {

    public function landing() {
        // Evaluate user-agent, exit if null
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        if (!$userAgent) exit;

        // Evaluate user role, login if not set
        if (!isset($_SESSION['user']['role'])) {
            $_SESSION['user']['role'] = 'Guest';
            return App::redirect('/login');
        }

        // Go home to evaluate the user role
        return App::redirect('/home');
    }

    public function home() {
        $auth = new AuthMiddleware();
        $auth->requireLogin();

        $books = App::getService('books')->findAllActiveBooks();
        $formatter = new BookFormatter();

        return App::view('main', [
            'books' => $formatter->formatMany($books),
            'canEdit' => App::getService('auth')->can('manageBooks')
        ]);
    }

    // public function landing() {
    //     $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    //     if (!$userAgent) {
    //         return App::redirect('/login');
    //     }

    //     if (empty($_SESSION['user']) || !isset($_SESSION['user']['role'])) {
    //         $_SESSION['user'] = ['role' => 'Guest'];
    //     }

    //     if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? 'Guest') === 'Guest') {
    //         return App::redirect('/login');
    //     }

    //     return $this->home();
    // }

    // public function home() {
    //     $bookFormatter = new \Ext\Controllers\Formatting\BookFormatter();
    //     if (!isset($_SESSION['user']) || $_SESSION['user']['role'] === 'Guest') {
    //         return App::redirect('/');
    //     }

    //     $books      = App::getService('books')->findAllActiveBooks();
    //     $formatted  = $bookFormatter->formatMany($books);

    //     return App::view('main', [
    //         'books' => $formatted ?? null,
    //         'canEdit' => App::getService('auth')->can('manageBooks')
    //     ]);
    // }
}