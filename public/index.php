<?php
declare(strict_types=1);
define('BASE_PATH', realpath(__DIR__ . '/..'));                         // Define a base project root path

require BASE_PATH . '/vendor/autoload.php';                            // Require the composer autoload file.
require BASE_PATH . '/app/helpers.php';                                // Require the helper function (might be a temp solution)

// Load the required namespaces
use Dotenv\Dotenv;
use App\Core\App;

// 1) Load environment variables from .env (no error if missing)
Dotenv::createImmutable(BASE_PATH)->safeLoad();

// 2) Load DB config array
$dbConfig  = require BASE_PATH . '/config/database.php';

// 3) Define paths
$schemaDir = BASE_PATH . '/schema';
$lockFile  = BASE_PATH . '/.installed.lock';

// 4) Boot the application (singleton)
App::boot($dbConfig, $schemaDir, $lockFile);

try {
    if(App::run()) {
        session_start(); // temp testing session
        // 5) Main entry point – replace with your Router or Runner
        $router = require BASE_PATH . '/config/routes.php';
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