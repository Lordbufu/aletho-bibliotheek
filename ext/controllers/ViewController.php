<?php
namespace Ext\Controllers;

// Re-factor status: tested and working
final class ViewController {
    private \App\App                                    $app;
    
    public function __construct() {
        $this->app          = new \App\App();
    }

    /** The initial landing route */
    public function landing(): void {
        $this->app::getService('auth')->uaIpChecker();

        if ($this->app::getService('auth')->isLoggedIn()) {
            $this->app::redirect('/home');
        }

        // a filter of sorts for unwanted/unexpected users or redirects session refresh/resets
        if (!isset($_SESSION['user'])) {
            $_SESSION['user'] = [
                'id'         => null,
                'name'       => 'Guest',
                'permission' => ['guest'],
                'ua_hash'    => null,
                'ip_hash'    => null
            ];
        }

        // codes testing section:
        // dd($this->app::getService('status')->getStatusForSelect());

        $this->app::view('main');
    }

    /** The main book catalog view for logged in users */
    public function home(): void {
        $this->app::getService('auth')->requireLogin();

        // codes testing section:
        // dd($this->app::getService('books')->testThis());
        // dd( $this->app::getService('loaner')->getActiveLoanerByBookId(13) );

        $this->app::view('main', [
            'books' => $this->app::getService('book')->getBooksForView()
        ]);
    }
}