<?php

return [
    'driver'    => $_ENV['DB_DRIVER']    ?? $_SERVER['DB_DRIVER']    ?? 'mysql',
    'host'      => $_ENV['DB_HOST']      ?? $_SERVER['DB_HOST']      ?? '127.0.0.1',
    'port'      => (int) ($_ENV['DB_PORT']      ?? $_SERVER['DB_PORT']      ?? 3306),
    'database'  => $_ENV['DB_NAME']      ?? $_SERVER['DB_NAME']      ?? '',
    'username'  => $_ENV['DB_USER']      ?? $_SERVER['DB_USER']      ?? '',
    'password'  => $_ENV['DB_PASS']      ?? $_SERVER['DB_PASS']      ?? '',
    'charset'   => $_ENV['DB_CHARSET']   ?? $_SERVER['DB_CHARSET']   ?? 'utf8mb4',
    'collation' => $_ENV['DB_COLLATION'] ?? $_SERVER['DB_COLLATION'] ?? 'utf8mb4_unicode_ci',
];