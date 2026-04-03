<?php
namespace Ext\Controllers;

use App\App;

final class AuthController {
    /** The login route for all users */
    public function login() {
        $val = App::getService('form_val')->validateLogin($_POST);

        if (!$val['valid']) {
            setFlash('inline', 'error', $val['errors']);
            setFlash('form', 'data', $val['data']);
            return App::redirect('/');
        }

        $clean = $val['data'];
        $result = App::getService('auth')->authenticate(
            $clean['userName'],
            $clean['password'],
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        );

        if (!$result->success) {
            setFlash('inline', 'error', [ 'credentials' => 'Ongeldige inloggegevens.' ]);
            setFlash('form', 'data', [ 'userName' => $clean['userName'] ?? '' ]);
            return App::redirect('/');
        }

        setFlash('global', 'success', 'Welkom ' . $clean['userName'] . ', veel plezier in de Bibliotheek!');
        return App::redirect('/home');
    }

    /** The logout route for all users */
    public function logout() {
        session_destroy();
        return App::redirect('/');
    }

    /** Password reset form route */
    public function resetPassword() {
        // Authenticate login state and user roles
        App::getService('auth')->requireRole(['office_admin', 'global_admin']);

        $input  = $_POST;
        $mode = isset($_POST['old_password']) ? 'self' : 'admin';

        switch ($mode) {
            case 'invalid':
                setFlash('inlinePop', 'error', 'Ongeldige aanvraag.');
                return App::redirect('/home#password-reset-popin');
            case 'self':
                $val = App::getService('form_val')->validatePasswordChange($input);
                break;
            case 'admin':
                $val = App::getService('form_val')->validatePasswordReset($input);
                break;
        }

        if (!$val['valid']) {
            setFlash('inlinePop', 'error', 'Wachtwoord wijzigen mislukt.');
            setFlash('form', 'data', $val['data']);
            return App::redirect('/home#password-reset-popin');
        }

        $clean = $val['data'];

        switch ($mode) {
            case 'self':
                $result = App::getService('user')->resetOwnPassword(
                    $_SESSION['user']['id'],
                    $clean['old_password'],
                    $clean['new_password']
                );

                if (!$result) {
                    setFlash('inlinePop', 'error', 'Oud wachtwoord klopt niet.');
                    return App::redirect('/home#password-reset-popin');
                }

                setFlash('global', 'success', 'Je wachtwoord is succesvol gewijzigd.');
                break;
            case 'admin':
                $result = App::getService('user')->resetPasswordForUser(
                    $_SESSION['user']['id'],
                    $clean['user_name'],
                    $clean['new_password']
                );

                if (!$result) {
                    setFlash('inlinePop', 'error', ['credentials' => 'Gebruiker niet gevonden.']);
                    return App::redirect('/home#password-reset-popin');
                }

                setFlash('global', 'success', 'Wachtwoord succesvol gereset.');
                break;
            default:
                throw new \RuntimeException('Unexpected mode after the input validation in: resetPassword()');
        }

        return App::redirect('/home');
    }
}