<?php
/** Temp mental note for status changes:
 *   // Manual status change events:
 *   'loan_confirm'          => ['status' => [2], 'from' => [1], 'trigger' => 'user_action', 'strict' => true],
 *   'pickup_ready_confirm'  => ['status' => [4], 'from' => [3], 'trigger' => 'user_action', 'strict' => true],
 *   'pickup_confirm'        => ['status' => [2], 'from' => [4], 'trigger' => 'user_action', 'strict' => true],
 *   'reserv_confirm'        => ['status' => [5], 'trigger' => 'user_action', 'strict' => false],
 *   'transport_request'     => ['status' => [3], 'trigger' => 'user_action', 'strict' => false],

 *   // Automated (logic driven) status change events:
 *   'reserv_confirm_auto'   => ['status' => [5], 'from' => [2], 'trigger' => 'auto_action', 'strict' => true],
 *   'transp_req_auto'       => ['status' => [3], 'from' => [2], 'trigger' => 'auto_action', 'strict' => true],

 *   // CRON status change events:
 *   'return_reminder'       => ['status' => [2], 'from' => [2], 'trigger' => 'cron_action', 'strict' => true],
 *   'overdue_reminder_user' => ['status' => [6], 'from' => [2], 'trigger' => 'cron_action', 'strict' => true],
 *   'overdue_notice_admin'  => ['status' => [6], 'from' => [2], 'trigger' => 'cron_action', 'strict' => true],
 */

namespace Ext\Controllers;

use App\App;

class StatusController {
    protected \App\Service\BooksService         $bookS;
    protected \App\Service\StatusService        $status;
    protected \App\Service\ValidationService    $valS;

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

    /** Return all book statuses as JSON. */
    public function requestStatus() {
        $statuses = $this->status->getAllStatuses('idType');
        header('Content-Type: application/json');
        echo json_encode($statuses);
    }

    /** Request all status details, so the `periode-wijzigen` popin can be polulated */
    public function requestPopStatus() {
        $statuses = $this->status->getAllStatuses();
        header('Content-Type: application/json');
        echo json_encode($statuses);
    }

    /** Return the status of a specific book as JSON. */
    public function requestBookStatus() {
        $bookId = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;
        $status = $this->status->getBookStatus($bookId, "type");
        header('Content-Type: application/json');
        echo json_encode([$status]);
    }

    // Still need to review these last functions, but i think there still good
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

        /* Sanitize and validate the form data */
        if (!$this->valS->validateStatusChangeForm($_POST)) {
            setFlash('js', 'book_id', $_POST['book_id']);
            setFlash('single', 'book_id', $_POST['book_id']);
            setFlash('inlinePop', 'data', $this->valS->errors());
            return App::redirect('/#change-book-status-popin');
        }

        /* Store the cleaned data */
        $clean = $this->valS->cleanData();

        /* Build potential loaner data before attemptin a status change */
        $cLoaner = [];
        if (!empty($clean['loaner_name'])) {
            $cLoaner = [
                'name'      => $clean['loaner_name'],
                'email'     => $clean['loaner_email'],
                'office'    => App::getService('offices')->getOfficeIdByName($clean['loaner_location'])
            ];
        }

        /* Attempt the status change */
        $result = $this->bookS->changeBookStatus(
            $clean['book_id'],
            $clean['status_id'],
            'user_action',
            $cLoaner
        );

        /* Evaluate the result, and act acordingly */
        if (!$result) {
            setFlash('single', 'book_id', $clean['book_id']);
            setFlash('global', 'failure', 'Book data kon niet verwerkt worden!');
            return App::redirect('/');
        }

        setFlash('single', 'book_id', $clean['book_id']);
        setFlash('global', 'success', 'Boekgegevens zijn bijgewerkt.');
        return App::redirect('/');
    }
}