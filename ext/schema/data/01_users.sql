-- regular default test user  // password_hash('Bib-welcome-25', PASSWORD_DEFAULT)
INSERT INTO `users`
    (`id`, `name`, `password`, `email`, `is_loaner`, `is_office_admin`, `is_global_admin`, `create_at`, `updated_at`, `active`)
        VALUES
    (NULL, 'Bibliotheek', '$2y$10$o3P95WU2xrKRfYUye4.DbOaocrJ4.//bf4GQPdjKwirZ4SpK2pvXy', 'test@aletho.nl', '1', '0', '0', current_timestamp(), current_timestamp(), '1'),
    (NULL, 'AdminAssen', '$2y$10$o3P95WU2xrKRfYUye4.DbOaocrJ4.//bf4GQPdjKwirZ4SpK2pvXy', 'test@aletho.nl', '0', '1', '0', current_timestamp(), current_timestamp(), '1'),
    (NULL, 'AdminGro', '$2y$10$o3P95WU2xrKRfYUye4.DbOaocrJ4.//bf4GQPdjKwirZ4SpK2pvXy', 'test@aletho.nl', '0', '1', '0', current_timestamp(), current_timestamp(), '1'),
    (NULL, 'Hoofdadmin', '$2y$10$o3P95WU2xrKRfYUye4.DbOaocrJ4.//bf4GQPdjKwirZ4SpK2pvXy', 'test@aletho.nl', '0', '0', '1', current_timestamp(), current_timestamp(), '1');