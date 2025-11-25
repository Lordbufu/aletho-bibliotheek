<?php

return [
    // Core App services
    'router' => [ 'class' => \App\Router::class, 'config' => BASE_PATH . '/ext/config/routes.php' ],
    'database' => [ 'class'  => \App\Database::class, 'config' => BASE_PATH . '/ext/config/database.php' ],
    'logger' => [ 'class'  => \App\Logger::class ],
    'auth' => [ 'class'  => \App\Auth::class ],
    'val' => [ 'class'  => \App\ValidationService::class ],
    // Library specific services
    'books' => [ 'class' => \App\BooksService::class ],
    'status' => [ 'class' => \App\StatusService::class ],
    'loaners' => [ 'class' => \App\LoanersService::class ],
    'offices' => [ 'class' => \App\OfficesService::class ],
    'mail' => [ 'class' => \App\MailTemplateService::class, 'config' => BASE_PATH . '/ext/config/mail.php' ],
    'notification' => [ 'class' => \App\NotificationService::class, 'config' => BASE_PATH . '/ext/config/mail.php' ]
];