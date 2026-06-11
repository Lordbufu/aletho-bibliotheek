<?php

namespace Ext\Controllers;

class StatusController {
    private \App\App                                    $app;
    
    public function __construct() {
        $this->app          = new \App\App();
    }

    // Re-factor status: tested and working
    /** XHR Request: Request a editable status list, and provide formatted data to the frontend */
    public function requestPopinStatus() {
        return $this->app::json(
            $this->app::getService('status')->getStatusForEdit()
        );
    }

    // Re-factor status: tested and working
    /** XHR Request: Request a selectable status list, and provide formatted data for the frontend */
    public function requestStatus() {
        return $this->app::json(
            $this->app::getService('status')->getStatusForSelect()
        );
    }

    // Everthing below here still needs a re-view/factor
    /** Edit the status period properties */
    public function editStatusPeriod() {
        // Authenticate login state and user roles
        $this->app::getService('auth')->requireRole(['office_admin', 'global_admin']);

        $validate = $this->app::getService('form_val')->validateStatusPeriod($_POST);

        if (!$validate['valid']) {
            setFlash('inlinePop', 'data', $validate['errors']);
            setFlash('form', 'message', $validate['data']);
            setFlash('js', 'status_type', $_POST['status_type'] ?? '');
            return $this->app::redirect('/home#status-period-popin');
        }

        $result = $this->app::getService('status')->updatePeriod($validate['data']);

        if (!$result) {
            setFlash('global', 'failure', 'Status periode kon niet worden bijgewerkt.');
            setFlash('form', 'message', $validate['data']);
            setFlash('js', 'status_type', $_POST['status_type'] ?? '');
            return $this->app::redirect('/home#status-period-popin');
        }

        setFlash('global', 'success', 'Status periode aangepast.');
        return $this->app::redirect('/home');
    }

    // TODO: Considering changing the name of `$_POST['status_type']` and its associated flows, as its a index field not a type field
    /** Change status requests for books */
    public function changeStatus() {
        $this->app::getService('auth')->requireRole(['office_admin', 'global_admin']);

        $validate   = $this->app::getService('form_val')->validateStatusChange($_POST);
        $trigger    = 'manual';

        if (!$validate['valid']) {
            if (isset($validate['errors']['book_id'])) {
                setFlash('global', 'failure', 'Status periode kon niet worden bijgewerkt.');
                return $this->app::redirect('/home');
            }

            setFlash('inlinePop', 'data', $validate['errors']);
            setFlash('form', 'message', $validate['data']);
            setFlash('js', 'status_type', $_POST['status_type'] ?? '');
            return $this->app::redirect('/home#change-book-status-popin');
        }

        $result = $this->app::getService('book_status')->changeStatus($validate['data'], $trigger);

        if (!$result->passed) {
            setFlash('global', 'success', $result->errorMessage);
            setFlash('form', 'message', $validate['data']);
            setFlash('js', 'status_type', $_POST['status_type'] ?? '');
            return $this->app::redirect('/home#change-book-status-popin');
        }

        // TODO: Remove null guard because user feedback should always be set, added for overdatum flow testing.
        setFlash('global', 'success', $result->userFeedbackMessage ?? null);
        return $this->app::redirect('/home');
    }
}

    // Re-factor status: Potentially redundant now
    // /** XHR Request for: To pre-fill the status-change pop-in, with the current active status as first <option> */
    // public function requestBookStatus() {
    //     $bookId = (int)$_GET['book_id'];
    //     $statusVM = $this->app->getService('status')->getStatusByBookId($bookId);
    //     return $this->app::json($statusVM->id);
    // }