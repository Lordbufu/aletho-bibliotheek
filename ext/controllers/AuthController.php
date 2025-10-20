<?php
namespace Ext\Controllers;

use App\{App, Auth};
use App\Validation\FormValidation;

/*  Handles authentication-related HTTP requests. */
class AuthController {
    protected Auth $auth;
    protected FormValidation $validator;

    /*  Construct Auth as default local service. */
    public function __construct() {
        try {
            $this->auth = new Auth();
            $this->validator = App::getService('val')->formVal();
        } catch(Exception $e) {
            App::getService('logger')->error("Failed to construct the `AuthController`: {$e->getLine()}", "controllers");
            error_log($e->getMessage(), 0);
        }
    }

    /*  Show the login form. */
    public function showForm() {
        /*  Basically a session timeout protection, going back to the landingpage route. */
        if (!isset($_SESSION['user'])) {
            return App::redirect('/');
        }

        return App::view('main');
    }

    /*  Handle login submission. */
    public function authenticate() {
        if (!$this->validator->validateUserLogin($_POST)) {
            setFlash('inline', 'failure', 'Vul zowel gebruikersnaam als wachtwoord in.');
            setFlash('form', 'data', ['userName' => $this->validator->cleanData()['userName'] ?? '']);
            return App::redirect('/login');
        }

        $data = $this->validator->cleanData();

        if (!$this->auth->login($data['userName'], $data['userPw'])) {
            setFlash('inline', 'failure', 'Ongeldige gebruikersnaam of wachtwoord.');
            setFlash('form', 'data', ['userName' => $data['userName']]);
            return App::redirect('/login');
        }

        return App::redirect('/');
    }

    /*  Handle logout. */
    public function logout() {
        $this->auth->logout();

        if (!isset($_SESSION['user'])) {
            $_SESSION['user']['role'] = 'Guest';
        }
        
        return App::redirect('/');
    }

    /*  Handle all password resets. */
    public function resetPassword() {
        $isGlobal = ($_SESSION['user']['role'] ?? '') === 'Gadmin';

        if (!$this->validator->validatePasswordChange($_POST, $isGlobal)) {
            setFlash('inline', 'failure', 'Controleer de invoer en probeer opnieuw.');
            setFlash('form', 'data', [
                'user_name' => $this->validator->cleanData()['user_name'] ?? ''
            ]);
            return App::redirect('/home#password-reset-popin');
        }

        $data = $this->validator->cleanData();

        if ($isGlobal) {
            $result = $this->auth->resetUserPassword(
                $data['user_name'],
                $data['new_password']
            );
        } else {
            $result = $this->auth->resetOwnPassword(
                $_SESSION['user']['id'],
                $data['current_password'],
                $data['new_password']
            );
        }

        setFlash('inline', $result['success'] ? 'success' : 'failure', $result['message']);
        return App::redirect('/home#password-reset-popin');
    }
}