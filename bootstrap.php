<?php   // Load/Require all externals so the index.php is cleaner.

define('BASE_PATH', realpath(__DIR__));
require BASE_PATH . '/vendor/autoload.php';
require BASE_PATH . '/ext/helpers.php';

use Dotenv\Dotenv;

Dotenv::createImmutable(BASE_PATH)->safeLoad();

$env = $_ENV['APP_ENV'] ?? 'production';

/**
 * Session Configuration:
 *  - Custom session cache storage.
 *  - Garbage Collection settings.
 *  - Secure cookies.
 */
$sesTempPath = BASE_PATH . '/ext/storage/cache';
if (!is_dir($sesTempPath)) { mkdir($sesTempPath, 0700, true); }
session_save_path($sesTempPath);
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);