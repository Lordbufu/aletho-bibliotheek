<?php
/** TODO List:
 *      - Review the add/edit authentication logic, i might want to change that to just checking there admin status.
 *      - Review the frontend JQuery for edit and add logic, there seems to be a few key issues atm:
 *          - Fields not being tagged as editable.
 *          - Input remains when converted to a Tag.
 *          - Requires more live testing.
 *      - Add a default status when adding books, seems logical to just set to avaible right ?
 */

namespace Ext\Controllers;

use App\{App, BooksService, ValidationService};

/*  Handles books related user logic. */
class BookController {
    protected BooksService $bookS;
    protected ValidationService $valS;

    /*  Construct BooksService as default local service. */
    public function __construct() {
        try {
            $this->bookS = new BooksService;
            $this->valS  = new ValidationService();
        } catch(Exception $e) {
            App::getService('logger')->error(
                "Failed to construct `BookService` or `ValidationService`",
                "BookController"
            );
            
            error_log($e.getMessage(), 0);
        }
    }

    /*  Get and return all potentialy known book writers/genres/offices, for form autocomplete features. */
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

    // TODO: Re-add old form data to session _flash ?
    /*  Filter and process book add form data, and call the add functions in the BooksService. */
    public function add() {
        $hasError   = false;
        $newData    = [];
        $result     = false;

        // dd($_POST);

        // Filter out unauthorized users first.
        if (!App::getService('auth')->can('manageBooks')) {
            setFlash('global', 'failure', 'Je hebt geen rechten om deze actie uit te voeren.');
            return App::redirect('/');
        }

        // Check if POST data exists and is 'sanitized'.
        if (empty($_POST) || !$this->valS->sanitizeInput($_POST, 'add')) {
            setFlash('failure', $this->valS->valErrors());
        } else {
            $newData = $this->valS->sanData();
        }

        //  Validate input, and return errors and old input on failure.
        if (!isset($_SESSION['_flash']['type']) && !$this->valS->validateBookForm($newData, 'add')) {
            setFlash('failure', $this->valS->valErrors());
        }

        // Attempt to update book data if no errosr where set.
        if (!isset($_SESSION['_flash']['type'])) {
            $result = $this->bookS->addBook($newData);
            
            if (!$result) {
                setFlash('failure', 'Boekgegevens zijn niet toegevoegd.');
            } else {
                setFlash('success', 'Boekgegevens zijn toegevoegd.');
            }
        }

        return App::redirect('/');
    }

    /** Filter and process book edit form data, and call the update function in the BooksService.
     *      @return void Redirects to the landing page route, so user lands on the default view again.
     */
    public function edit() {
        $hasError   = false;
        $newData    = [];
        $result     = false;

        if (!App::getService('auth')->can('manageBooks')) {
            setFlash('failure', 'Je hebt geen rechten om deze actie uit te voeren.');
            return App::redirect('/');
        }

        if (empty($_POST) || !$this->valS->sanitizeInput($_POST, 'edit')) {
            $bookId = isset($_POST['book_id']) && is_numeric($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
            setFlash('failure', $this->valS->valErrors());
        } else {
            $newData = $this->valS->sanData();
        }

        if (!isset($_SESSION['_flash']['type']) && !$this->valS->validateBookForm($newData, 'edit')) {
            setFlash('failure', $this->valS->valErrors());
        }

        if (!isset($_SESSION['_flash']['type'])) {
            $result = $this->bookS->updateBook($newData);
            
            if (!$result) {
                setFlash('failure', 'Boekgegevens zijn niet bijgewerkt.');
            } else {
                setFlash('success', 'Boekgegevens zijn bijgewerkt.');
            }
        }

        return App::redirect('/');
    }

    public function delete() {
        dd($_POST);
    }
}