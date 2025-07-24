<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\App;

Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();   // 1) Load .env into $_ENV & $_SERVER (won’t error if missing)

$dbConfig  = require __DIR__ . '/../config/database.php';   // 2) Load DB config

$schemaDir = __DIR__ . '/../schema';
$lockFile  = __DIR__ . '/../.installed.lock';
$app       = new App($dbConfig, $schemaDir, $lockFile); // 3) Boot your App

$app->run();    // 4) Run it (will install tables once, then “App ready to go.”)