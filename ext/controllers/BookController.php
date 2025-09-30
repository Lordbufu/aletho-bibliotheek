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
    public function bookdata() {
        $type = $_GET['data'] ?? '';
        header('Content-Type: application/json; charset=utf-8');
        if ($type === 'writers') {
            echo json_encode($this->bookS->getWritersForDisplay());
        } elseif ($type === 'genres') {
            echo json_encode($this->bookS->getGenresForDisplay());
        } elseif ($type === 'offices') {
            echo json_encode($this->bookS->getOfficesForDisplay());
        } else {
            echo json_encode([]);
        }
        exit;
    }

    /**
     * Expected book data format:
     *      [_method] => PATCH
     *      [book_id] => 1
     *      [book_name] => Text Book 001
     *      [book_writers] => Array ( [0] => Test Writer 001, )
     *      [book_genres] => Array ( [0] => Programmeren, )
     *      [book_offices] => Array ( [0] => Assen, )
     */
    public function edit() {
        if (!isset($_POST['_method']) || $_POST['_method'] !== 'PATCH' || $_POST['book_id'] < 1) {
            return App::redirect('/');
        }

        $bookId = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
        $bookName = isset($_POST['book_name']) ? trim(strip_tags($_POST['book_name'])) : '';
        $writers = isset($_POST['book_writers']) && is_array($_POST['book_writers']) ? array_map('strip_tags', $_POST['book_writers']) : [];
        $genres = isset($_POST['book_genres']) && is_array($_POST['book_genres']) ? array_map('strip_tags', $_POST['book_genres']) : [];
        $offices = isset($_POST['book_offices']) && is_array($_POST['book_offices']) ? array_map('strip_tags', $_POST['book_offices']) : [];

        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] === 'Guest') {
            return App::redirect('/');
        }

        if (! $_SESSION['user']['canEdit']) {
            return App::redirect('/home');
        }

        // TODO: Write update function, and adjust associated libraries as well, to support the data and its links.
        $result = $this->bookS->updateBook($bookId, [
            'title' => $bookName,
            'writers' => $writers,
            'genres' => $genres,
            'offices' => $offices
        ]);

        // TODO: Add user feedback.
        return App::redirect('/home');
    }
}