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

    public function requestStatus() {
        $statuses = $this->bookS->getAllStatuses();
        header('Content-Type: application/json');
        echo json_encode($statuses);
    }

    public function changeStatus()/*: Response */{
        if (!App::getService('auth')->can('manageBooks')) {
            setFlash('global', 'failure', 'Je hebt geen rechten om deze actie uit te voeren.');
            return App::redirect('/');
        }

        if (!$this->valS->validateStatusChangeForm($_POST)) {
            setFlash('inlinePop', 'data', $this->valS->errors());
            return App::redirect('/#change-book-status-popin');
        }

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

            $ok = $this->bookS->setBookStatus(
                $bookId,
                $statusId,
                null,       // metaId
                $loanerId,
                null,       // locationId (future)
                false       // sendMail
            );

            if (!$ok) {
                throw new \RuntimeException('Status kon niet worden bijgewerkt');
            }

            $this->db->finishTransaction();

            setFlash('single', 'book_id', $bookId);
            setFlash('global', 'success', 'Boekgegevens zijn bijgewerkt.');
            return App::redirect('/');
        } catch (\Throwable $t) {
            $this->db->cancelTransaction();
            setFlash('global', 'failure', 'Book data kon niet verwerkt worden!');
            setFlash('single', 'book_id', $bookId);
            return App::redirect('/');
        }
    }
}