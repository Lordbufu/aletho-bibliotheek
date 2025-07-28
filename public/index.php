<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\App;

// 1) Load environment variables from .env (no error if missing)
Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

// 2) Load DB config array
$dbConfig  = require __DIR__ . '/../config/database.php';

// 3) Define paths
$schemaDir = __DIR__ . '/../schema';
$lockFile  = __DIR__ . '/../.installed.lock';

// 4) Boot the application (singleton)
App::boot($dbConfig, $schemaDir, $lockFile);

try {
    if(App::run()) {
        // 5) Main entry point – replace with your Router or Runner
        // e.g. App::router()->dispatch();
        $router = require __DIR__ . '/../config/routes.php';
        $router->dispatch();
    } else {
        echo 'App initialization failed.';
    }
} catch (\Throwable $e) {
    // Single place to log or render fatal errors
    App::logger()->error($e->getMessage());
    http_response_code(500);
    echo '❌ Fatal error: ' . $e->getMessage();
    exit(1);
}