<?php
namespace Ext\Controllers;

use App\App;

class StatusController {
    protected \App\BooksService         $bookS;
    protected \App\StatusService        $status;
    protected \App\ValidationService    $valS;

    /*  Construct App services as default local service. */
    public function __construct() {
        try {
            $this->bookS    = App::getService('books');
            $this->valS     = App::getService('val');
            $this->status   = App::getService('status');
        } catch (\Throwable $t) {
            throw $t;
        }
    }

    /*  Return all book statuses as JSON. */
    public function requestStatus() {
        $statuses = $this->status->getAllStatuses();
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
        $result = $this->status->updateStatusPeriod(
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
        /* Authenticate the user rights */
        if (!App::getService('auth')->can('manageBooks')) {
            setFlash('global', 'failure', 'Je hebt geen rechten om deze actie uit te voeren.');
            return App::redirect('/');
        }

        // dd($_POST);

        /* Sanitize and validate the form data */
        if (!$this->valS->validateStatusChangeForm($_POST)) {
            setFlash('js', 'book_id', $_POST['book_id']);
            setFlash('single', 'book_id', $_POST['book_id']);
            setFlash('inlinePop', 'data', $this->valS->errors());
            return App::redirect('/#change-book-status-popin');
        }

        /* Store the cleaned data */
        $clean = $this->valS->cleanData();

        /* Build loaner data before sending it off */
        if (isset($clean['loaner_name'])) {
            $cLoaner = [
                'loaner_name'       => $clean['loaner_name'],
                'loaner_email'      => $clean['loaner_email'],
                'loaner_location'   => App::getService('offices')->getOfficeIdByName($clean['loaner_location'])
            ];
        }

        /* Send of data to the correct function. */
        if (isset($statusId) && $statusId === 1) {
            $result = $this->status->setBookStatus($clean['book_id'], $clean['status_id']);
        } else {
            $result = $this->bookS->changeBookStatus($clean['book_id'], $clean['status_id'], $cLoaner);
        }


        dd("Result is: " . $result);

        if (!$result) {
            setFlash('global', 'failure', 'Book data kon niet verwerkt worden!');
            setFlash('single', 'book_id', $clean['book_id']);
            return App::redirect('/');
        }

        setFlash('single', 'book_id', $clean['book_id']);
        setFlash('global', 'success', 'Boekgegevens zijn bijgewerkt.');
        return App::redirect('/');
    }
}