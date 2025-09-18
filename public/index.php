<?php
require __DIR__ . '/../bootstrap.php';

use App\App;

if (!App::boot()) {                                         // Attempt to boot the App
    // dd(App::getBootErrors());                               // debug logging option
    handleBootFailure(App::getBootErrors());                // Log\Displays errors if failed
    exit;
}

session_start();                                            // Start default session here
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;           // Set user_agent if present or set to null

if ($userAgent) {                                           // Default user-agent check
    if (empty($_SESSION['user'])) {                         // Set role to 'Guest' for initial login routing
        $_SESSION['user'] = ['role' => 'Guest'];
    }
    
    if (!isset($_SESSION['user']['role'])) {                // Included check for lost\altered session data
        echo 'no user data found';    
        exit;
    }
} else {
    echo 'no user-agent found';
    exit;
}

App::getService('router')->dispatch();                      // Router requests