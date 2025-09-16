<?php
require __DIR__ . '/../bootstrap.php';

use App\App;

if (!App::boot()) {
    // dd(App::getBootErrors());
    handleBootFailure(App::getBootErrors());
    exit;
}

// Start session, and check for a user-agent
session_start();
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

// If a user agent is set, we consider the user valid, and set a guest tag if no user data is set.
if ($userAgent) {
    if (empty($_SESSION['user'])) {
        $_SESSION['user'] = ['role' => 'Guest'];
    }
} else {
    echo 'no user-agent found';
    exit;
}

App::getService('router')->dispatch();