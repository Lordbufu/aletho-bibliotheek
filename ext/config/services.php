<?php

return [
    // Core App services
    'router' => [
        'class' => \App\Router::class,
        'config' => BASE_PATH . '/ext/config/routes.php'
    ],
    'database' => [
        'class'  => \App\Database::class,
        'config' => BASE_PATH . '/ext/config/database.php'
    ],
    'logger' => [
        'class'  => \App\Logger::class
    ],
    'auth' => [
        'class'  => \App\Auth::class
    ],
    // View & Flow Specific Services
    'books' => [
        'class' => \App\Service\BooksService::class
    ],
    'loaners' => [
        'class' => \App\Service\LoanersService::class
    ],
    'mail' => [
        'class' => \App\Service\MailTemplateService::class,
        'config' => BASE_PATH . '/ext/config/mail.php'
    ],
    'notification' => [
        'class' => \App\Service\NotificationService::class,
        'config' => array_merge(
                ['mailConfig' => require BASE_PATH . '/ext/config/mail.php'],
                ['statusEventMap' => require BASE_PATH . '/ext/config/notification.php']
            )
    ],
    'offices' => [
        'class' => \App\Service\OfficesService::class
    ],
    'status' => [
        'class' => \App\Service\StatusService::class
    ],
    'val' => [
        'class'  => \App\Service\ValidationService::class
    ],
];