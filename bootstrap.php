<?php   // Load/Require all externals so the index.php is cleaner.

define('BASE_PATH', realpath(__DIR__));
require BASE_PATH . '/vendor/autoload.php';
require BASE_PATH . '/ext/helpers.php';

use Dotenv\Dotenv;

Dotenv::createImmutable(BASE_PATH)->safeLoad();