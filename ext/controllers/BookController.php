<?php

namespace Ext\Controllers;

use App\App;

class BookController {
    /** Request bookData for the frontend input suggestions (Tested & Working) */
    public function bookData() {
        $type = $_GET['data'] ?? '';
        $data = App::getService('books')->getBookFormData($type);
        return App::json($data);
    }

    /** Process add book requests (Tested & Working) */
    public function addBook() {
        // Authenticate login state and user roles
        App::getService('auth')->requireRole(['office_admin', 'global_admin']);

        $validate = App::getService('form_val')->validateBookForm($_POST, 'add');

        if (!$validate['valid']) {
            setFlash('inlinePop', 'data', $validate['errors']);
            return App::redirect('/#add-book-popin');
        }

        $data = $validate['data'];
        $result  = App::getService('books')->addBook($data);

        if (!$result) {
            setFlash('global', 'failure', 'Boekgegevens zijn niet toegevoegd.');
            return App::redirect('/#add-book-popin');
        }

        setFlash('global', 'success', 'Boekgegevens zijn toegevoegd.');
        return App::redirect('/home');
    }

    /** Process edit book requests (Tested & Working) */
    public function editBook() {
        // Authenticate login state and user roles
        App::getService('auth')->requireRole(['office_admin', 'global_admin']);

        $validate   = App::getService('form_val')->validateBookForm($_POST, 'edit');
        $bookId     = (int)($_POST['book_id'] ?? 0);

        if (!$bookId) {
            setFlash('global', 'failure', 'Ongeldig boek ID.');
            return App::redirect('/home');
        }

        if (!$validate['valid']) {
            setFlash('single', 'book_id', $bookId);
            setFlash('inline', 'data', $validate['errors']);
            return App::redirect('/home');
        }

        $data       = $validate['data'];
        $result     = App::getService('books')->editBook($bookId, $data);

        if (!$result) {
            setFlash('single', 'book_id', $bookId);
            setFlash('global', 'failure', 'Boek kon niet worden bijgewerkt.');
            return App::redirect('/home');
        }

        setFlash('global', 'success', 'Boek succesvol bijgewerkt.');
        return App::redirect('/home');
    }

    /** Process delelete book requests (Tested & Working) */
    public function deleteBook() {
        // Authenticate login state and user roles
        App::getService('auth')->requireRole(['office_admin', 'global_admin']);

        $bookId = (int)($_POST['book_id'] ?? 0);

        if (!$bookId) {
            setFlash('global', 'failure', 'Ongeldig boek ID.');
            return App::redirect('/home');
        }

        $result = App::getService('books')->deleteBook($bookId);

        if (!$result) {
            setFlash('single', 'book_id', $bookId);
            setFlash('global', 'failure', 'Boek kon niet worden gedeactiveerd.');
            return App::redirect('/home');
        }

        setFlash('global', 'success', 'Boek is gedeactiveerd.');
        return App::redirect('/home');
    }
}