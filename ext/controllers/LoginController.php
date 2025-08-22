<?php

namespace ext\controllers;

use App\App;

class LoginController {
    public function landing() {
        return App::view('auth/login');
    }

    public function showForm() {
        App::view('auth/login', ['error' => $_SESSION['login_error'] ?? null]);
        unset($_SESSION['login_error']);
    }

    public function authenticate() {
        $username = trim($_POST['userName'] ?? '');
        $password = $_POST['userPw'] ?? '';

        if (App::getService('auth')->login($username, $password)) {
            header('Location: /home');
            exit;
        }

        $_SESSION['login_error'] = 'Ongeldige gebruikersnaam of wachtwoord.';
        header('Location: /login');
        exit;
    }

    public function logout() {
        App::getService('auth')->logout();
        header('Location: /login');
        exit;
    }
}