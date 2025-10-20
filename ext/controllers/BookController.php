<?php
/** TODO List:
 *      - Review the frontend JQuery for edit and add logic, there seems to be a few key issues atm:
 *          - Tags not being added currently, most other functions seem to work now ?
 *          - Requires more live testing.
 *      - Add a default status when adding books, seems logical to just set to avaible right ?
 *      - Figure out how to redirect back to a book details dropdown, i used to have this functionality.
 *          - only required for the edit function, used needs to return to the book he/she was editing.
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

    /*  Filter and process book add form data, and call the add functions in the BooksService. */
    public function add() {
        $hasError   = false;
        $newData    = [];
        $result     = false;


        // Filter out unauthorized users first.
        if (!App::getService('auth')->can('manageBooks')) {
            setFlash('global', 'failure', 'Je hebt geen rechten om deze actie uit te voeren.');
            return App::redirect('/');
        }

        // Check if POST data exists and is 'sanitized'.
        if (empty($_POST) || !$this->valS->sanitizeInput($_POST, 'add')) {
            $error = $this->valS->valErrors();
            setFlash('global', 'failure', $error['book_id']);
            return App::redirect('/#add-book-popin');
        } else {
            $newData = $this->valS->sanData();
        }

        //  Validate input, and return errors and old input on failure.
        if (!isset($_SESSION['_flash']['type']) && !$this->valS->validateBookForm($newData, 'add')) {
            setFlash('inlinePop', 'data', $this->valS->valErrors());
            return App::redirect('/#add-book-popin');
        }

        // Attempt to update book data if no errosr where set.
        if (!isset($_SESSION['_flash']['type'])) {
            $result = $this->bookS->addBook($newData);
            
            if (!$result) {
                setFlash('global', 'failure', 'Boekgegevens zijn niet toegevoegd.');
            } else {
                setFlash('global', 'success', 'Boekgegevens zijn toegevoegd.');
                return App::redirect('/home');
            }
        }

        setFlash('global', 'failure', 'Boekgegevens zijn niet toegevoegd.');

        return App::redirect('/#add-book-popin');
    }

    /** Filter and process book edit form data, and call the update function in the BooksService.
     *      @return void Redirects to the landing page route, so user lands on the default view again.
     */
    public function edit() {
        $hasError   = false;
        $newData    = [];
        $result     = false;

        if (!App::getService('auth')->can('manageBooks')) {
            setFlash('global', 'failure', 'Je hebt geen rechten om deze actie uit te voeren.');
            return App::redirect('/');
        }

        if (empty($_POST) || !$this->valS->sanitizeInput($_POST, 'edit')) {
            $bookId = isset($_POST['book_id']) && is_numeric($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
            setFlash('inline', 'failure', $this->valS->valErrors());
        } else {
            $newData = $this->valS->sanData();
        }

        if (!isset($_SESSION['_flash']['type']) && !$this->valS->validateBookForm($newData, 'edit')) {
            foreach($this->valS->valErrors() as $key => $value) {
                $tempKeys[] = $key;
                $tempValues[] = $value;
            }

            setFlash('inlinePop', 'data', $this->valS->valErrors());
            return App::redirect('/');
        }

        if (!isset($_SESSION['_flash']['type'])) {
            $result = $this->bookS->updateBook($newData);
            
            if (!$result) {
                setFlash('global', 'failure', 'Boekgegevens zijn niet bijgewerkt.');
                setFlash('form', 'data', $newData);
                return App::redirect('/');
            } else {
                setFlash('global', 'success', 'Boekgegevens zijn bijgewerkt.');
            }
        }

        return App::redirect('/');
    }

    public function delete() {
        dd($_POST);
    }
}