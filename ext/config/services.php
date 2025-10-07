<?php

return [
    // Core router service
    'router' => [
        'class' => \App\Router::class,
        'config' => BASE_PATH . '/ext/config/routes.php'
    ],
    // Core database service
    'database' => [
        'class'  => \App\Database::class,
        'config' => BASE_PATH . '/ext/config/database.php'
    ],
    // Core logger service
    'logger' => [
        'class'  => \App\Logger::class
    ],
    // Core authentication service
    'auth' => [
        'class'  => \App\Auth::class
    ],
    // Core validation service
    'val' => [
        'class'  => \App\Validation\FormValidation::class
    ],
    // Core Books Service
    'books' => [
        'class' => \App\BooksService::class
    ]
];