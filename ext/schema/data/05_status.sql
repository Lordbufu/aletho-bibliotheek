-- Insert default status data
INSERT INTO `status`
    (`type`, `periode_length`, `reminder_day`, `overdue_day`, `active`)
VALUES
    ('Aanwezig', NULL, NULL, NULL, '1'),
    ('Afwezig', '14', '3', '1', '1'),
    ('Transport', '7', '1', '1', '1'),
    ('Ligt Klaar', '7', '1', '1', '1'),
    ('Gereserveerd', '7', '3', '1', '1'),
    ('Overdatum', NULL, NULL, NULL, '1');