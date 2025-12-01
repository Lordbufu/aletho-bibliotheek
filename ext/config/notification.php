<?php
return [
    // Status ID → Notification targets
    2 => ['user' => 'loan_confirm'],                                    // Afwezig
    5 => ['user' => 'reserv_confirm'],                                  // Gereserveerd
    3 => ['office' => 'transport_request'],                             // Transport
    4 => ['user' => 'pickup_ready_confirm'],                            // Ligt Klaar
    6 => ['user' => 'overdue_reminder', 'office' => 'overdue_notice'],  // Overdatum
];