<?php

namespace ext\controllers;

use App\App;

class AuthController {
    public function showForm() {
        // $error = $_SESSION['login_error'] ?? null;
        // unset($_SESSION['login_error']);
        return App::view('auth/login');
    }

    public function authenticate() {
        $username = trim($_POST['userName'] ?? '');
        $password = $_POST['userPw'] ?? '';

        if (App::getService('auth')->login($username, $password)) {

            return App::redirect('/home');
        }

        $_SESSION['_flash']['login_error'] = 'Ongeldige gebruikersnaam of wachtwoord.';
        return App::redirect('/login');
    }

    public function logout() {
        App::getService('auth')->logout();
        return App::redirect('/login');
    }

    public function resetPassword() {
        if (isset($_POST['current_password'])) {    // Office Admin
            $message = App::getService('auth')
                ->resetOwnPassword(
                    $_SESSION['user']['id'],
                    $_POST['current_password'],
                    $_POST['new_password']);
            $_SESSION['_flash'] = $message;
        }

        if (isset($_POST['user_name'])) {       // Global Admin
            $message = App::getService('auth')
                ->resetUserPassword(
                    $_POST['user_name'],
                    $_POST['new_password'],
                    $_POST['confirm_password']);
            $_SESSION['_flash'] = $message;
        }

        return App::redirect('/home#password-reset-popin');
    }
}