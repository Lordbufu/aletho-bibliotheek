<?php
namespace Ext\Controllers;

use App\{App, Database, BooksService, LoanersService, ValidationService};

class StatusController {
    protected BooksService      $bookS;
    protected LoanersService    $loaners;
    protected ValidationService $valS;
    protected Database          $db;

    /*  Construct App services as default local service. */
    public function __construct() {
        try {
            $this->bookS    = App::getService('books');
            $this->loaners  = App::getService('loaners');
            $this->valS     = App::getService('val');
            $this->db       = App::getService('database');
        } catch (\Throwable $t) {
            throw $t;
        }
    }

    /*  Return all book statuses as JSON. */
    public function requestStatus() {
        $statuses = $this->bookS->getAllStatuses();
        header('Content-Type: application/json');
        echo json_encode($statuses);
    }

    /*  Return the status of a specific book as JSON. */
    public function requestBookStatus() {
        $bookId = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;
        $book = $this->bookS->getBookById($bookId);
        $status = ['type' => $book['status']];
        header('Content-Type: application/json');
        echo json_encode([$status]);
    }

    /*  Set the period settings for a status. */
    public function setStatusPeriod()/*: Response */ {
        if (!App::getService('auth')->can('manageBooks')) {
            setFlash('global', 'failure', 'Je hebt geen rechten om deze actie uit te voeren.');
            return App::redirect('/');
        }

        if (!$this->valS->validateStatusPeriod($_POST)) {
            setFlash('inlinePop', 'data', $this->valS->errors());
            setFlash('form', 'message', $this->valS->cleanData());
            setFlash('js', 'status_type', $_POST['status_type'] ?? '');
            return App::redirect('/#status-period-popin');
        }

        $clean = $this->valS->cleanData();
        $result = $this->bookS->updateStatusPeriod(
            $clean['status_type'],
            $clean['periode_length'],
            $clean['reminder_day'],
            $clean['overdue_day']
        );

        if (!$result) {
            setFlash('global', 'failure', 'Statusperiode kon niet worden bijgewerkt.');

            $safeInput = [
                'periode_length' => $clean['periode_length'],
                'reminder_day'   => $clean['reminder_day'],
                'overdue_day'    => $clean['overdue_day']
            ];
            setFlash('form', 'message', $safeInput);
            setFlash('js', 'status_type', $_POST['status_type'] ?? '');

            return App::redirect('/#status-period-popin');
        }

        setFlash('global', 'success', 'Statusperiode succesvol bijgewerkt.');
        return App::redirect('/');
    }

    /*  Change the status of a book. */
    public function changeStatus()/*: Response */{
        if (!App::getService('auth')->can('manageBooks')) {
            setFlash('global', 'failure', 'Je hebt geen rechten om deze actie uit te voeren.');
            return App::redirect('/');
        }

        if (!$this->valS->validateStatusChangeForm($_POST)) {
            setFlash('js', 'book_id', $_POST['book_id']);
            setFlash('single', 'book_id', $_POST['book_id']);
            setFlash('inlinePop', 'data', $this->valS->errors());
            return App::redirect('/#change-book-status-popin');
        }

        $clean      = $this->valS->cleanData();
        $bookId     = $clean['book_id'];
        $statusId   = $clean['status_id'];

        $result = $this->loaners->changeBookStatus(
            $bookId,
            $statusId,
            $clean['loaner_name'],
            $clean['loaner_email']
        );

        if (!$result) {
            setFlash('global', 'failure', 'Book data kon niet verwerkt worden!');
            setFlash('single', 'book_id', $bookId);
            return App::redirect('/');
        }

        setFlash('single', 'book_id', $bookId);
        setFlash('global', 'success', 'Boekgegevens zijn bijgewerkt.');
        return App::redirect('/');
    }
}