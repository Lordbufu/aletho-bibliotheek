<?php
namespace Ext\Controllers;

// Re-factor status: tested and working
final class AuthController {
    private \App\App $app;

    public function __construct() {
        $this->app          = new \App\App();
    }

    /** Helper: Tiny redirect helper to reduce repeated code */
    private function redirectToPopin() {
        return $this->app::redirect('/home#password-reset-popin');
    }

    /** The login route for all users */
    public function login() {
        $val = $this->app::getService('form_val')->validateLogin($_POST);

        if (!$val['valid']) {
            setFlash('inline', 'error', $val['errors']);
            setFlash('form', 'data', $val['data']);
            return $this->app::redirect('/');
        }

        $clean = $val['data'];
        $userId = $this->app::getService('auth')->authenticate(
            $clean['identifier'],
            $clean['password'],
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        );

        if (!$userId) {
            setFlash('inline', 'error', [ 'credentials' => 'Ongeldige inloggegevens.' ]);
            setFlash('form', 'data', [ 'userName' => $clean['identifier'] ?? '' ]);
            return $this->app::redirect('/');
        }

        setFlash('global', 'success', 'Welkom ' . $_SESSION['user']['name'] . ', veel plezier in de Bibliotheek!');
        return $this->app::redirect('/home');
    }

    /** The logout route for all users */
    public function logout() {
        session_destroy();
        return $this->app::redirect('/');
    }

    /** Password reset form route */
    public function resetPassword() {
        $this->app::getService('auth')->requireRole(['office_admin', 'global_admin']);

        $input          = $_POST;
        $isSelf         = isset($input['current_password']);
        $mode           = $isSelf ? 'self' : 'admin';

        $validator      = $this->app::getService('form_val');
        $val = $isSelf
            ? $validator->validatePasswordChange($input)
            : $validator->validatePasswordReset($input);

        if (!$val['valid']) {
            setFlash('inlinePop', 'error', 'Wachtwoord wijzigen mislukt.');
            setFlash('form', 'data', $val['data']);
            return $this->redirectToPopin();
        }

        $clean          = $val['data'];
        $userService    = $this->app::getService('user');

        if ($isSelf) {
            $result = $userService->resetOwnPassword(
                $_SESSION['user']['id'],
                $clean['current_password'],
                $clean['new_password']
            );

            if (!$result) {
                setFlash('inlinePop', 'error', 'Oud wachtwoord klopt niet.');
                return $this->redirectToPopin();
            }

            setFlash('global', 'success', 'Je wachtwoord is succesvol gewijzigd.');
        } else {
            $result = $userService->resetPasswordForUser(
                $_SESSION['user']['id'],
                $clean['user_name'],
                $clean['new_password']
            );

            if (!$result) {
                setFlash('inlinePop', 'error', ['credentials' => 'Gebruiker niet gevonden.']);
                return $this->redirectToPopin();
            }

            setFlash('global', 'success', 'Wachtwoord succesvol gereset.');
        }

        return $this->app::redirect('/home');
    }
}