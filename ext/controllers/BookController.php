<?php

namespace Ext\Controllers;

class BookController {
    private \App\App                                        $app;
    
    public function __construct() {
        $this->app          = new \App\App();
    }

    // Re-factor status: tested and working
    /** Request bookData for the frontend input suggestions */
    public function bookData() {
        $data = [];
        $type = $_GET['data'] ?? '';

        switch($type) {
            case "schrijvers":
                $data = $this->app::getService('writer')->getWritersForView();
                break;
            case "genres":
                $data = $this->app::getService('genre')->getGenresForView();
                break;
            case "locaties":
                $data = $this->app::getService('location')->getLocationsForView();
                break;
        }

        return $this->app::json($data);
    }
    
    /** Process add book requests */
    public function addBook() {
        $this->app::getService('auth')->requireRole(['office_admin', 'global_admin']);
        $validate = $this->app::getService('form_val')->validateBookForm($_POST, 'add');

        if (!$validate['valid']) {
            setFlash('inlinePop', 'data', $validate['errors']);
            return $this->app::redirect('/#add-book-popin');
        }

        $data = $validate['data'];
        $result  = $this->app::getService('book')->addBook($data);

        if (!$result) {
            setFlash('global', 'failure', 'Boekgegevens zijn niet toegevoegd.');
            return $this->app::redirect('/#add-book-popin');
        }

        setFlash('global', 'success', 'Boekgegevens zijn toegevoegd.');
        return $this->app::redirect('/home');
    }

    /** Process edit book requests */
    public function editBook() {
        // Authenticate login state and user roles
        $this->app::getService('auth')->requireRole(['office_admin', 'global_admin']);

        $validate   = $this->app::getService('form_val')->validateBookForm($_POST, 'edit');
        $bookId     = (int)($_POST['book_id'] ?? 0);

        if (!$bookId) {
            setFlash('global', 'failure', 'Ongeldig boek ID.');
            return $this->app::redirect('/home');
        }

        if (!$validate['valid']) {
            setFlash('single', 'book_id', $bookId);
            setFlash('inline', 'data', $validate['errors']);
            return $this->app::redirect('/home');
        }

        $data       = $validate['data'];
        $result     = $this->app::getService('book')->editBook($bookId, $data);

        if (!$result) {
            setFlash('single', 'book_id', $bookId);
            setFlash('global', 'failure', 'Boek kon niet worden bijgewerkt.');
            return $this->app::redirect('/home');
        }

        setFlash('global', 'success', 'Boek succesvol bijgewerkt.');
        return $this->app::redirect('/home');
    }

    /** Process delelete book requests */
    public function deleteBook() {
        // Authenticate login state and user roles
        $this->app::getService('auth')->requireRole(['office_admin', 'global_admin']);

        $bookId = (int)($_POST['book_id'] ?? 0);

        if (!$bookId) {
            setFlash('global', 'failure', 'Ongeldig boek ID.');
            return $this->app::redirect('/home');
        }

        $result = $this->app::getService('book')->deleteBook($bookId);

        if (!$result) {
            setFlash('single', 'book_id', $bookId);
            setFlash('global', 'failure', 'Boek kon niet worden gedeactiveerd.');
            return $this->app::redirect('/home');
        }

        setFlash('global', 'success', 'Boek is gedeactiveerd.');
        return $this->app::redirect('/home');
    }
}