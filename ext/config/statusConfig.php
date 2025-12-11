<?php

return [
    'loan_confirm' => [
        'roles' => ['user'],
        'status' => [2], // Afwezig
        'trigger' => 'user_action'
    ],
    'pickup_ready_confirm' => [
        'roles' => ['user'],
        'status' => [4], // Ligt Klaar
        'trigger' => 'user_action'
    ],
    'pickup_confirm' => [
        'roles' => ['user'],
        'status' => [4, 2], // Ligt Klaar => Afwezig
        'trigger' => 'user_action'
    ],
    'return_reminder' => [
        'roles' => ['user'],
        'status' => [2], // Afwezig
        'trigger' => 'cron_near_due'
    ],
    'reserv_confirm' => [
        'roles' => ['user'],
        'status' => [5], // Gereserveerd
        'trigger' => 'user_action'
    ],
    'overdue_reminder' => [
        'roles' => ['admin', 'user'],
        'status' => [2, 6], // Afwezig => Overdatum
        'trigger' => 'cron_overdue'
    ],
    'transport_request' => [
        'roles' => ['admin'],
        'status' => [3], // Transport
        'trigger' => 'user_action'
    ]
    // 'overdue_notice' => [
    //     'roles' => ['admin', 'user'],
    //     'status' => [2, 6], // Afwezig => Overdatum
    //     'trigger' => 'cron_overdue'
    // ]
];