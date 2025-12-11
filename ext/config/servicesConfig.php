<?php

return [
    // Core App services
    'router' => [
        'class' => \App\Router::class,
        'config' => BASE_PATH . '/ext/config/routes.php'
    ],
    'database' => [
        'class'  => \App\Database::class,
        'config' => BASE_PATH . '/ext/config/databaseConfig.php'
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
        'config' => BASE_PATH . '/ext/config/mailTemplateConfig.php'
    ],
    'notification' => [
        'class' => \App\Service\NotificationService::class,
        'config' => BASE_PATH . '/ext/config/notificationConfig.php'
    ],
    'offices' => [
        'class' => \App\Service\OfficesService::class
    ],
    'status' => [
        'class' => \App\Service\StatusService::class,
        'config' => BASE_PATH . '/ext/config/statusConfig.php'
    ],
    'val' => [
        'class'  => \App\Service\ValidationService::class
    ],
];