<?php
namespace Ext\Controllers;

final class ViewController {
    private \Ext\Controllers\Formatter\BookFormatter    $formatter;
    private \App\App                                    $app;
    
    public function __construct() {
        $this->formatter    = new \Ext\Controllers\Formatter\BookFormatter();
        $this->app          = new \App\App();
    }

    /** The initial landing route (Tested & Working) */
    public function landing(): void {
        $this->app::getService('auth')->uaIpChecker();

        if ($this->app::getService('auth')->isLoggedIn()) {
            $this->app::redirect('/home');
        }

        if (!isset($_SESSION['user']['role'])) {
            $_SESSION['user']['role'] = 'Guest';
        }

        $this->app::view('main');
    }

    /** The main book catalog view for logged in users (Tested & Working) */
    public function home(): void {
        $this->app::getService('auth')->requireLogin();

        $books      = $this->app::getService('books')->getBooksForView();
        $formatted  = $this->formatter->formatMany($books);

        $this->app::view('main', ['books' => $formatted]);
    }
}