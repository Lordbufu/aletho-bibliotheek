INSERT INTO `status`
    (`status_name`, `status_length`, `status_reminder`, `status_overdue`, `is_facade`, `filter_mode`, `reservation_behavior`)
VALUES
    -- Statuses that represent the period data
    ('Aanwezig', NULL, NULL, NULL, '1', NULL, NULL),
    ('Uitgeleend', '14', '3', '1', '1', 'edit', NULL),
    ('Ligt Klaar', '7', '3', '1', '1', 'edit', NULL),
    ('Gereserveerd', '7', '3', '1', '1', 'edit', NULL),
    ('Overdatum', '7', '3', '1', '1', 'edit', NULL),
    ('Transport', '7', '3', '1', '1', 'edit', NULL),
    -- Statuses intended for ease of use
    ('Uitlenen', NULL, NULL, NULL, '0', 'select', NULL),
    ('Ingeleverd', NULL, NULL, NULL, '0', 'select', NULL),
    ('Aangekomen', NULL, NULL, NULL, '0', 'select', NULL),
    ('Reserveren', NULL, NULL, NULL, '0', 'select', '{"createsReservation": true, "notificationTypeId": "reserv_confirm", "requiresLoaner": true, "requiresOffice": true, "autoActivateOn": ["Ingeleverd", "Aangekomen"] }');