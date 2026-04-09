<?php

return [
    // Core App services, already cleaned and changed.
    'router'        => [
        'class'     => \App\Router::class,
        'config'    => BASE_PATH . '/ext/config/routes.php'
    ],
    'database'      => [
        'class'     => \App\Database::class,
        'config'    => BASE_PATH . '/ext/config/databaseConfig.php'
    ],

    // Authentication and User related services
    'auth'          => [                                      // Changed/Cleaned service 
        'class'     => \App\Services\AuthService::class
    ],
    'user'          => [                                      // Changed/Cleaned service
        'class'     => \App\Services\UserService::class
    ],

    // Validation services
    'form_val'      => [                                      // Changed/Cleaned service
        'class'     => \App\Validation\FormValidator::class
    ],

    // View & Flow Specific Services
    'books'         => [                                      // Changed/Cleaned service
        'class'     => \App\Services\BookService::class
    ],

    'book_status'   => [                                      // Changed/Cleaned service
        'class'     => \App\Services\BookStatusService::class 
    ],

    'offices'      => [                                       // Changed/Cleaned service
        'class'     => \App\Services\OfficesService::class
    ],    

    'statuses'      => [                                      // Changed/Cleaned service
        'class'     => \App\Services\StatusService::class
    ],

    'loan'      => [                                          // Changed/Cleaned service
        'class'     => \App\Services\LoanService::class
    ],

    'loaner'      => [                                        // Changed/Cleaned service
        'class'     => \App\Services\LoanerService::class
    ],

    // Re-view to complete the refactor process.
    'notifications' => [
        'class' => \App\Services\NotificationService::class,
        'config' => BASE_PATH . '/ext/config/notificationConfig.php'
    ],
    
    'mail' => [
        'class' => \App\Services\MailTemplateService::class,
        'config' => BASE_PATH . '/ext/config/mailTemplateConfig.php'
    ],


    // Extra functionality
    // 'logger' => [
    //     'class'  => \App\Services\LoggerService::class
    // ],
];