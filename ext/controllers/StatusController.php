<?php

namespace Ext\Controllers;

class StatusController {
    private \App\App                                    $app;
    
    public function __construct() {
        $this->app          = new \App\App();
    }

    // Re-factor status: tested and working
    /** XHR Request: Request a editable status list, and provide formatted data to the frontend. */
    public function requestPopinStatus() {
        $this->app::getService('auth')->requireRole(['office_admin', 'global_admin']);
        
        return $this->app::json(
            $this->app::getService('status')->getStatusForEdit()
        );
    }

    // Re-factor status: tested and working
    /** XHR Request: Request a selectable status list, and provide formatted data for the frontend */
    public function requestStatus() {
        $this->app::getService('auth')->requireRole(['office_admin', 'global_admin']);

        return $this->app::json(
            $this->app::getService('status')->getStatusForSelect()
        );
    }

    // Re-factor status: tested and working
    /** Edit the status period properties */
    public function editStatusPeriod() {
        $this->app::getService('auth')->requireRole(['office_admin', 'global_admin']);
        $validate = $this->app::getService('form_val')->validateStatusPeriod($_POST);

        if (!$validate['valid']) {
            setFlash('inlinePop', 'data', $validate['errors']);
            setFlash('form', 'message', $validate['data']);
            setFlash('js', 'status_id', $_POST['status_id'] ?? '');
            return $this->app::redirect('/home#status-period-popin');
        }

        $result = $this->app::getService('status')->updatePeriod($validate['data']);

        if (!$result['valid']) {
            setFlash('global', 'failure', $result['message']);
            setFlash('js', 'status_id', $_POST['status_id'] ?? '');
            return $this->app::redirect('/home#status-period-popin');
        }

        setFlash('global', 'success', 'Status periode aangepast.');
        return $this->app::redirect('/home');
    }

    // Re-factor status: W.I.P.
    /** Change status requests for books */
    public function changeStatus() {
        $this->app::getService('auth')->requireRole(['office_admin', 'global_admin']);

        $validate   = $this->app::getService('form_val')->validateStatusChange($_POST);

        if (!$validate['valid']) {
            if (isset($validate['errors']['book_id'])) {
                setFlash('global', 'failure', 'Status periode kon niet worden bijgewerkt.');
                return $this->app::redirect('/home');
            }

            setFlash('inlinePop', 'data', $validate['errors']);
            setFlash('form', 'message', $validate['data']);
            setFlash('js', 'status_id', $_POST['status_id'] ?? '');
            return $this->app::redirect('/home#change-book-status-popin');
        }

        $result = $this->app::getService('book_status')->changeStatus($validate['data']);
        dd($result);

        if (!$result->passed) {
            setFlash('global', 'success', $result->errorMessage);
            setFlash('form', 'message', $validate['data']);
            setFlash('js', 'status_id', $_POST['status_id'] ?? '');
            return $this->app::redirect('/home#change-book-status-popin');
        }

        setFlash('global', 'success', $result->userFeedbackMessage ?? null);
        return $this->app::redirect('/home');
    }
}