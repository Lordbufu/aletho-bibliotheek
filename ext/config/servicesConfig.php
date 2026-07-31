<?php

return [
    // Config based services
    'router'        => [
        'class'     => \App\Router::class,
        'config'    => BASE_PATH . '/ext/config/routes.php'
    ],
    'database'      => [
        'class'     => \App\Database::class,
        'config'    => BASE_PATH . '/ext/config/databaseConfig.php'
    ],

    // Non-config auth/validation services
    'auth'          => [ 'class' => \App\Services\AuthService::class ],
    'form_val'      => [ 'class' => \App\Validation\FormValidator::class ],

    // Non-config core services
    'user'          => [ 'class' => \App\Services\UserService::class ],
    'location'      => [ 'class' => \App\Services\LocationService::class ],
    'book'          => [ 'class' => \App\Services\BookService::class ],
    'writer'        => [ 'class' => \App\Services\WriterService::class],
    'genre'         => [ 'class' => \App\Services\GenreService::class],
    'status'        => [ 'class' => \App\Services\StatusService::class ],

    'loaner'        => [ 'class' => \App\Services\LoanerService::class ],

    // Re-factor status: W.I.P.
    'book_status'   => [ 'class' => \App\Services\BookStatusService::class ],
    'notifications' => [
        'class' => \App\Services\NotificationService::class,
        'config' => BASE_PATH . '/ext/config/notificationConfig.php'
    ],
    'mail'          => [ 'class' => \App\Services\MailTemplateService::class ],
    // 'logger'        => [ 'class' => \App\Services\LoggerService::class ],       // Extra functionality for later

    // Legacy services
    //'loan'          => [ 'class' => \App\Services\LoanService::class ],
];