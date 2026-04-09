<?php
namespace Ext\Controllers;

use App\App;

/** Handles books related user logic */
class BookController {
    protected \App\Service\BooksService         $bookS;
    protected \App\Service\OfficesService       $offices;
    protected \App\Service\ValidationService    $valS;

    /** Construct App services as default local service */
    public function __construct() {
        try {
            $this->bookS    = App::getService('books');
            $this->offices  = App::getService('offices');
            $this->valS     = App::getService('val');
        } catch(\Throwable $t) {
            throw $t;
        }
    }

    /** Get and return all potentialy known book writers/genres/offices, for form autocomplete features */
    public function bookdata() {
        $type = $_GET['data'] ?? '';

        header('Content-Type: application/json; charset=utf-8');

        if ($type === 'writers') {
            echo json_encode($this->bookS->getWritersForDisplay());
        } elseif ($type === 'genres') {
            echo json_encode($this->bookS->getGenresForDisplay());
        } elseif ($type === 'offices') {
            echo json_encode($this->offices->getOfficesForDisplay());
        } else {
            echo json_encode([]);
        }

        exit;
    }

    /** Filter and process book add form data, and call the add functions in the BooksService 
     * 
     *  TODO's:
     *      - Remove hardcoded 0 key for `$tempData['book_offices'][0]`, if/when multiple office are implemented.
     *          - Or adjust the 'cleanData()' output to be a single name only.
     *      - Additionally, once everything is finalized, remove the need to set $newData to new key strings.
     */
    public function add() {
        if (!App::getService('auth')->can('manageBooks')) {
            setFlash('global', 'failure', 'Je hebt geen rechten om deze actie uit te voeren.');
            return App::redirect('/');
        }

        if (empty($_POST) || !$this->valS->sanitizeInput($_POST, 'add')) {
            $errors = $this->valS->errors();
            if (!empty($errors['book_id'])) {
                setFlash('global', 'failure', 'Geen geldige book data ontvangen !');
                return App::redirect('/');
            }

            setFlash('global', 'failure', 'Book data kon niet verwerkt worden!');
            return App::redirect('/#add-book-popin');
        }

        $tempData = $this->valS->cleanData(); 

        if (!isset($_SESSION['_flash']['type']) && !$this->valS->validateBookForm($tempData, 'add')) {
            setFlash('inlinePop', 'data', $this->valS->errors());
            return App::redirect('/#add-book-popin');
        }

        $newData    = [
            'title'     => $tempData['book_name'],
            'writers'   => $tempData['book_writers'],
            'genres'    => $tempData['book_genres'],
            'office'    => $tempData['book_offices'][0]
        ];

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

    /** Filter and process book edit form data, and call the update function in the BooksService */
    public function edit() {
        if (!App::getService('auth')->can('manageBooks')) {
            setFlash('global', 'failure', 'Je hebt geen rechten om deze actie uit te voeren.');
            return App::redirect('/');
        }

        if (empty($_POST) || !$this->valS->sanitizeInput($_POST, 'edit')) {
            $errors = $this->valS->errors();
            if (!empty($errors['book_id'])) {
                setFlash('global', 'failure', 'Geen geldige book data ontvangen !');
                return App::redirect('/');
            }

            $bookId = (int) $_POST['book_id'];
            setFlash('single', 'book_id', $bookId);
            setFlash('global', 'failure', 'Book data kon niet verwerkt worden!');
            return App::redirect('/');
        }

        $tempData = $this->valS->cleanData();

        if (!isset($_SESSION['_flash']['type']) && !$this->valS->validateBookForm($tempData, 'edit')) {
            foreach($this->valS->errors() as $key => $value) {
                $tempKeys[] = $key;
                $tempValues[] = $value;
            }

            setFlash('inlinePop', 'data', $this->valS->errors());
            return App::redirect('/');
        }

        $newData = [
            'id'        => $tempData['book_id'],
            'title'     => $tempData['book_name'],
            'writers'   => $tempData['book_writers'],
            'genres'    => $tempData['book_genres'],
            'offices'   => $tempData['book_offices'][0]
        ];

        if (!isset($_SESSION['_flash']['type'])) {
            $result = $this->bookS->updateBook($newData);

            if (!$result) {
                setFlash('global', 'failure', 'Boekgegevens zijn niet bijgewerkt.');
                setFlash('form', 'data', $newData);
            } else {
                setFlash('global', 'success', 'Boekgegevens zijn bijgewerkt.');
            }
        }

        return App::redirect('/');
    }

    /** Authenticate and filter data, then set book to inactive */
    public function delete() {
        if (!App::getService('auth')->can('manageBooks')) {
            setFlash('global', 'failure', 'Je hebt geen rechten om deze actie uit te voeren.');
            return App::redirect('/');
        }

        if (empty($_POST) || !$this->valS->sanitizeInput($_POST, 'delete')) {
            setFlash('global', 'failure', 'Geen geldige book data ontvangen !');
            return App::redirect('/');
        }

        $bookId = (int) $_POST['book_id'];
        $result = $this->bookS->swapBookActiveState($bookId);

        if (!$result) {
            setFlash('global', 'failure', 'Boek kon niet worden verwijderd!');
        } else {
            setFlash('global', 'success', 'Boek is verwijderd!');
        }

        return App::redirect('/');
    }
}