<?php

namespace Ext\Controllers;

use App\App;

class StatusController {
    /** XHR Request for: Current active statuses, or a specific status by id, for the popin <select> elements */
    public function requestPopinStatus() {
        $id = $_GET['id'] ?? null;

        if ($id) {
            $status = App::getService('statuses')->getStatusRowById((int)$id);
            return App::json($status);
        }

        $statuses = App::getService('statuses')->getEditableStatuses();
        return App::json($statuses);
    }

    /** XHR Request for: provide the unfiltered list of status types */
    public function requestStatus() {
        $statusTypes = App::getService('statuses')->getAllFormatted();
        return App::json($statusTypes);
    }

    /** XHR Request for: To pre-fill the status-change pop-in, with the current active status as first <option> */
    public function requestBookStatus() {
        $bookStatusCtx = App::getService('book_status')->loadBookStatusContext($_GET['book_id']);
        return App::json($bookStatusCtx->status['type']);
    }

    /** Edit the status period properties */
    public function editStatusPeriod() {
        // Authenticate login state and user roles
        App::getService('auth')->requireRole(['office_admin', 'global_admin']);

        $validate = App::getService('form_val')->validateStatusPeriod($_POST);

        if (!$validate['valid']) {
            setFlash('inlinePop', 'data', $validate['errors']);
            setFlash('form', 'message', $validate['data']);
            setFlash('js', 'status_type', $_POST['status_type'] ?? '');
            return App::redirect('/home#status-period-popin');
        }

        $result = App::getService('statuses')->updatePeriod($validate['data']);

        if (!$result) {
            setFlash('global', 'failure', 'Status periode kon niet worden bijgewerkt.');
            setFlash('form', 'message', $validate['data']);
            setFlash('js', 'status_type', $_POST['status_type'] ?? '');
            return App::redirect('/home#status-period-popin');
        }

        setFlash('global', 'success', 'Status periode aangepast.');
        return App::redirect('/home');
    }

    // TODO: Considering changing the name of `$_POST['status_type']` and its associated flows, as its a index field not a type field
    /** Change status requests for books */
    public function changeStatus() {
        App::getService('auth')->requireRole(['office_admin', 'global_admin']);

        $validate   = App::getService('form_val')->validateStatusChange($_POST);
        $trigger    = 'manual';

        if (!$validate['valid']) {
            if (isset($validate['errors']['book_id'])) {
                setFlash('global', 'failure', 'Status periode kon niet worden bijgewerkt.');
                return App::redirect('/home');
            }

            setFlash('inlinePop', 'data', $validate['errors']);
            setFlash('form', 'message', $validate['data']);
            setFlash('js', 'status_type', $_POST['status_type'] ?? '');
            return App::redirect('/home#change-book-status-popin');
        }

        $result = App::getService('book_status')->changeStatus($validate['data'], $trigger);

        if (!$result->passed) {
            setFlash('global', 'success', $result->errorMessage);
            setFlash('form', 'message', $validate['data']);
            setFlash('js', 'status_type', $_POST['status_type'] ?? '');
            return App::redirect('/home#change-book-status-popin');
        }

        // TODO: Remove null guard because user feedback should always be set, added for overdatum flow testing.
        setFlash('global', 'success', $result->userFeedbackMessage ?? null);
        return App::redirect('/home');
    }
}