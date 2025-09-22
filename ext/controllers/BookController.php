<?php

namespace Ext\Controllers;

use App\{App, BooksService};

/**
 * Handles books related user logic.
 */
class BookController {
    protected BooksService $bookS;

    /**
     * Construct BooksService as default local service.
     */
    public function __construct() {
        $this->bookS = new BooksService;
    }

    /**
     * Get and return all potentialy known book writers.
     * For the UI/UX autocomplete features on the admin side of things.
     * 
     * @return json (array of strings)
     */
    public function writers() {
        $writers = $this->bookS->getWritersForDisplay();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($writers);
        exit;
    }

    /**
     * Expected book data format:
     *      [_method] => PATCH
     *      [book_id] => 1
     *      [book_name] => Text Book 001
     *      [book_writers] => Array ( [0] => Test Writer 001, )
     */
    public function edit() {
        if (!isset($_POST['_method'])) {
            return App::redirect('/');
        }

        dd($_POST);
    }
}