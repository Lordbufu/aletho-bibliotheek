<?php   // Load/Require all externals so the index.php is cleaner.

define('BASE_PATH', realpath(__DIR__));
require BASE_PATH . '/vendor/autoload.php';
require BASE_PATH . '/ext/helpers.php';

/**
 * Set enviroment related config stuff.
 */
use Dotenv\Dotenv;
Dotenv::createImmutable(BASE_PATH)->safeLoad();
$env = $_ENV['APP_ENV'] ?? 'production';

/**
 * Create & set session cache file path, and set garbage collection.
 */
$sesTempPath = BASE_PATH . '/ext/storage/cache';
if (!is_dir($sesTempPath)) {
    mkdir($sesTempPath, 0700, true);
}
session_save_path($sesTempPath);
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);

/**
 * Turn on, create & set custom php error log location.
 */
$logTempPath = BASE_PATH . '/ext/storage/php-log';
if (!is_dir($logTempPath)) {
    mkdir($logTempPath, 0700, true);
}
ini_set('log_errors', 'On');
ini_set('error_log', $logTempPath . '/custom_error.log');

/* These should only be on, or set to all in the Development enviroment */
ini_set('display_errors', 'On');                            // ensure this is disable on production.
ini_set('display_startup_errors', 'On');                    // ensure this is disable on production.
error_reporting(E_ALL);                                     //

/**
 * Set secure session cookies.
 */
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);