-- // password_hash('Bib-welcome-25', PASSWORD_DEFAULT)
INSERT INTO `users`
    (`user_name`, `user_email`, `user_password`, `permission_flags`)
VALUES
    ('Bibliotheek', 'noreply.bibliotheek@aletho.nl',    '$2y$10$N.xWfPUGxtTmR53jayws4O/hvtfCF8ZAhf/k3f2Re7bn1IGW9d756', '["viewer"]'),
    ('AdminAssen',  'assen.bibliotheek@aletho.nl',      '$2y$10$N.xWfPUGxtTmR53jayws4O/hvtfCF8ZAhf/k3f2Re7bn1IGW9d756', '["viewer", "office_admin"]'),
    ('AdminGro',    'groningen.bibliotheek@aletho.nl',  '$2y$10$N.xWfPUGxtTmR53jayws4O/hvtfCF8ZAhf/k3f2Re7bn1IGW9d756', '["viewer", "office_admin"]'),
    ('HoofdAdmin',  'bibliotheek@aletho.nl',            '$2y$10$N.xWfPUGxtTmR53jayws4O/hvtfCF8ZAhf/k3f2Re7bn1IGW9d756', '["viewer", "global_admin"]');