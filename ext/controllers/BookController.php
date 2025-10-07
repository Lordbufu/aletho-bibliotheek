<?php

/*  Dealing with errrors and user feedback:
 *      $_SESSION['_flash'] = [
 *          'type'      => 'failure'|'success', 
 *          'message'   => '...'
 *      ]
 */

namespace Ext\Controllers;

use App\{App, BooksService, ValidationService};

/** Handles books related user logic. */
class BookController {
    protected BooksService $bookS;
    protected ValidationService $valS;

    /** Construct BooksService as default local service. */
    public function __construct() {
        $this->bookS = new BooksService;
        $this->valS  = new ValidationService();

        App::getService('logger')->info(
            "Controller 'BookController' has constructed 'BooksService' and 'ValidationService'",
            'bookcontroller'
        );
    }

    /** Get and return all potentialy known book writers/genres/offices, for form autocomplete features.
     *      @return void Outputs JSON and exits.
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

    /** Filter and process book add form data, and call the add functions in the BooksService.
     *      @return void Redirects to the landing page route, so user lands on the default view again.
     */
    public function add() {
        $this->bookS->addBook([]);
    }

    /** Filter and process book edit form data, and call the update function in the BooksService.
     *      @return void Redirects to the landing page route, so user lands on the default view again.
     */
    public function edit() {
        $hasError   = false;
        $newData    = [];
        $result     = false;

        // Filter out unauthorized users first.
        if (!App::getService('auth')->canUpdateInfo()) {
            $hasError = setFlash([
                'type'      => 'failure',
                'message'   => 'Je hebt geen rechten om deze actie uit te voeren.'
            ]);
            return App::redirect('/');
        }

        // Check if POST data exists and is 'sanitized'.
        if (empty($_POST) || !$this->valS->sanitizeInput($_POST)) {
            $bookId = isset($_POST['book_id']) && is_numeric($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
            $hasError = setFlash([
                'type'      => 'failure',
                'message'   => $this->valS->valErrors(),
                'old'       => ['book_id' => $bookId]
            ]);
        } else {
            $newData = $this->valS->sanData();
        }

        //  Validate input, and return errors and old input on failure.
        if (!$hasError && !$this->valS->validateBookForm($newData, 'edit')) {
            $hasError = setFlash([
                'type'      => 'failure',
                'message'   => $this->valS->valErrors(),
                'old'       => $newData
            ]);
        }

        // Attempt to update book data if no errosr where set.
        if (!$hasError) {
            $result = $this->bookS->updateBook($newData);
            
            if (!$result) {
                $hasError = setFlash([
                    'type'      => 'failure',
                    'message'   => 'Boekgegevens zijn niet bijgewerkt.',
                    'old'       => $newData
                ]);
            } else {
                $hasError = setFlash([
                    'type'      => 'success',
                    'message'   => 'Boekgegevens zijn bijgewerkt.'
                ]);
            }
        }

        return App::redirect('/');
    }
}