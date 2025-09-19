<?php

namespace Ext\Controllers;

use App\{App, Auth};

/**
 * Handles authentication-related HTTP requests.
 */
class AuthController {
    protected Auth $auth;

    public function __construct() {
        $this->auth = new Auth();
    }

    /**
     * Show the login form.
     */
    public function showForm() {
        return App::view('auth/login');
    }

    /**
     * Handle login submission.
     */
    public function authenticate() {
        $username = trim($_POST['userName'] ?? '');
        $password = $_POST['userPw'] ?? '';

        if ($username === '' || $password === '') {
            App::getService('logger')->warning('Login attempt with missing credentials', 'auth');
            return App::view('auth/login', ['error' => 'Please enter both username and password.']);
        }

        if ($this->auth->login($username, $password)) {
            App::getService('logger')->info("User {$username} logged in", 'auth');
            return App::redirect('/'); // Redirect to home or dashboard
        }

        App::getService('logger')->warning("Failed login for {$username}", 'auth');
        return App::view('auth/login', ['error' => 'Invalid username or password.']);
    }

    /**
     * Handle logout.
     */
    public function logout() {
        $this->auth->logout();
        App::getService('logger')->info('User logged out', 'auth');
        return App::redirect('/login');
    }

    /**
     * Handle password reset (for own account).
     */
    public function resetPassword() {
        $userId         = $_SESSION['user']['id'] ?? null;
        $currentPw      = $_POST['currentPw'] ?? '';
        $newPw          = $_POST['newPw'] ?? '';
        $confirmNewPw   = $_POST['confirmNewPw'] ?? '';

        if (!$userId) {
            App::getService('logger')->error('Password reset attempted without user session', 'auth');
            $_SESSION['_flash'] = ['error' => 'Not logged in.'];
        }

        if ($newPw !== $confirmNewPw) {
            $_SESSION['_flash'] = ['error' => 'New passwords do not match.'];
        }

        if (!isset($_SESSION['flash']['error'])) {
            $result = $this->auth->resetOwnPassword($userId, $currentPw, $newPw);
        }

        if (isset($result['error'])) {
            $_SESSION['_flash'] = $message;
        }

        return App::redirect('/home#password-reset-popin');
    }
}