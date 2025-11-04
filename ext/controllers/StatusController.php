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
    public function setStatusPeriod() {
        // Auth check (copy-paste from changeStatus if needed)
        if (!App::getService('auth')->can('manageBooks')) {
            setFlash('global', 'failure', 'Je hebt geen rechten om deze actie uit te voeren.');
            return App::redirect('/');
        }

        // Validate input
        $requiredFields = ['status_type'];
        $errors = [];

        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $errors[$field] = 'Dit veld is verplicht.';
            }
        }

        // Sanitize and validate optional fields
        $periode_length = isset($_POST['periode_length']) && $_POST['periode_length'] !== '' ? (int)$_POST['periode_length'] : null;
        $reminder_day   = isset($_POST['reminder_day']) && $_POST['reminder_day'] !== '' ? (int)$_POST['reminder_day'] : null;
        $overdue_day    = isset($_POST['overdue_day']) && $_POST['overdue_day'] !== '' ? (int)$_POST['overdue_day'] : null;

        // Additional validation (optional: check for positive integers)
        if ($periode_length !== null && $periode_length < 0) {
            $errors['periode_length'] = 'Moet een positief getal zijn.';
        }

        if ($reminder_day !== null && $reminder_day < 0) {
            $errors['reminder_day'] = 'Moet een positief getal zijn.';
        }

        if ($overdue_day !== null && $overdue_day < 0) {
            $errors['overdue_day'] = 'Moet een positief getal zijn.';
        }

        if ($errors) {
            setFlash('inlinePop', 'data', $errors);

            // Only store sanitized input if it passed basic sanitation
            $safeInput = [
                'periode_length' => $periode_length,
                'reminder_day'   => $reminder_day,
                'overdue_day'    => $overdue_day
            ];
            setFlash('form', 'message', $safeInput);
            setFlash('js', 'status_type', $_POST['status_type'] ?? '');

            return App::redirect('/#status-period-popin');
        }

        // Update status in DB
        $statusId = (int)$_POST['status_type'];

        try {
            $this->db->startTransaction();

            // Update the status record
            $result = $this->bookS->updateStatusPeriod(
                $statusId,
                $periode_length,
                $reminder_day,
                $overdue_day
            );

            if (!$result) {
                throw new \RuntimeException('Statusperiode kon niet worden bijgewerkt.');
            }

            $this->db->finishTransaction();

            setFlash('global', 'success', 'Statusperiode succesvol bijgewerkt.');
            return App::redirect('/');
        } catch (\Throwable $t) {
            $this->db->cancelTransaction();
            throw $t;

            setFlash('global', 'failure', 'Statusperiode kon niet worden bijgewerkt.');

            // Only store sanitized input if it passed basic sanitation
            $safeInput = [
                'periode_length' => $periode_length,
                'reminder_day'   => $reminder_day,
                'overdue_day'    => $overdue_day
            ];
            setFlash('form', 'message', $safeInput);
            setFlash('js', 'status_type', $_POST['status_type'] ?? '');

            return App::redirect('/#status-period-popin');
        }
    }

    /*  Change the status of a book. */
    public function changeStatus()/*: Response */{
        if (!App::getService('auth')->can('manageBooks')) {
            setFlash('global', 'failure', 'Je hebt geen rechten om deze actie uit te voeren.');
            return App::redirect('/');
        }

        // dd(!$this->valS->validateStatusChangeForm($_POST));
        if ($this->valS->validateStatusChangeForm($_POST)) {
            setFlash('js', 'book_id', $_POST['book_id']);
            setFlash('single', 'book_id', $_POST['book_id']);
            // setFlash('inlinePop', 'data', $this->valS->errors());
            setFlash('inlinePop', 'data', ['change_status_type' => 'test']);
            // dd($_SESSION);
            return App::redirect('/#change-book-status-popin');
        }

        dd('test2');

        $clean      = $this->valS->cleanData();
        $bookId     = $clean['book_id'];
        $statusId   = $clean['status_id'];

        try {
            $this->db->startTransaction();

            $loaner = $this->loaners->findByEmail($clean['loaner_email']);

            if (!$loaner) {
                $loaner = $this->loaners->create($clean['loaner_name'], $clean['loaner_email']);
            }
            $loanerId = $loaner['id'];

            $result = $this->bookS->setBookStatus(
                $bookId,
                $statusId,
                null,
                $loanerId,
                null,
                false
            );

            if (!$result) { throw new \RuntimeException('Status kon niet worden bijgewerkt'); }

            $this->db->finishTransaction();

            setFlash('single', 'book_id', $bookId);
            setFlash('global', 'success', 'Boekgegevens zijn bijgewerkt.');
            return App::redirect('/');
        } catch (\Throwable $t) {
            $this->db->cancelTransaction();
            throw $t;

            setFlash('global', 'failure', 'Book data kon niet verwerkt worden!');
            setFlash('single', 'book_id', $bookId);

            return App::redirect('/');
        }
    }
}