<?php

return [
    'env'   => $_ENV['APP_ENV']   ?? $_SERVER['APP_ENV']   ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
    'url'   => 'https://localhost',
];