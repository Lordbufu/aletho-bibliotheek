-- regular default test user  // password_hash('Bib-welcome-25', PASSWORD_DEFAULT)
INSERT INTO `users` (`name`, `password`, `email`, `is_loaner`, `is_office_admin`, `is_global_admin`, `active`) VALUES
    ('Bibliotheek', '$2y$10$N.xWfPUGxtTmR53jayws4O/hvtfCF8ZAhf/k3f2Re7bn1IGW9d756', 'test@aletho.nl', '1', '0', '0', '1'),
    ('AdminAssen', '$2y$10$N.xWfPUGxtTmR53jayws4O/hvtfCF8ZAhf/k3f2Re7bn1IGW9d756', 'test@aletho.nl', '0', '1', '0', '1'),
    ('AdminGro', '$2y$10$N.xWfPUGxtTmR53jayws4O/hvtfCF8ZAhf/k3f2Re7bn1IGW9d756', 'test@aletho.nl', '0', '1', '0', '1'),
    ('HoofdAdmin', '$2y$10$N.xWfPUGxtTmR53jayws4O/hvtfCF8ZAhf/k3f2Re7bn1IGW9d756', 'test@aletho.nl', '0', '0', '1', '1');