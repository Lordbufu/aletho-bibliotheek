-- Insert default status data (not correctly filled in yet)
INSERT INTO `status` (`id`, `type`, `periode_length`, `reminder_day`, `overdue_day`, `active`) VALUES
    (NULL, 'Aanwezig', NULL, NULL, NULL, '1'),
    (NULL, 'Afwezig', '14', '3', '1', '1'),
    (NULL, 'Transport', '7', '1', NULL, '1'),
    (NULL, 'Gereserveerd', NULL, '3', '1', '1'),
    (NULL, 'Overdatum', '1', '1', '1', '1');