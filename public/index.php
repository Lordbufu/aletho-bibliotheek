<?php
require __DIR__ . '/../bootstrap.php';

use App\App;

session_start();

if (!App::boot()) {
    echo "Application failed to boot. Please check logs.";
    exit;
}

App::getService('router')->dispatch();