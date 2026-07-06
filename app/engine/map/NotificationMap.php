<?php
namespace App\Engine\Map;

final class NotificationMap {
    /** Global: Pre-defined map for status_id -> notification_type */
    public static array $map = [
        'Afwezig' => [
            'Aanwezig'   => 'loan_confirm',
            'Ligt Klaar' => 'pickup_confirm',
        ],

        'Transport'    => 'transport_request',
        'Ligt Klaar'   => 'pickup_ready_confirm',
        'Gereserveerd' => 'reserv_confirm',
        'Return'       => 'return_reminder',
        'Overdatum'    => 'overdue_reminder_user'
    ];

    /** API: Resolve the correct notification tag */
    public static function resolveNotificationType(string $to, string $from): ?string {
        // Case 1: 2D map (Afwezig)
        if (isset(self::$map[$to]) && is_array(self::$map[$to])) {
            return self::$map[$to][$from] ?? null;
        }

        // Case 2: simple 1D map
        return self::$map[$to] ?? null;
    }
}