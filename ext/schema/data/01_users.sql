-- regular default test user  // password_hash('bib-welcome-25', PASSWORD_DEFAULT)
INSERT INTO `users` (`id`, `name`, `password`, `email`, `is_loaner`, `is_office_admin`, `is_global_admin`, `create_at`, `updated_at`, `active`)
VALUES (NULL, 'user', '$2y$10$o3P95WU2xrKRfYUye4.DbOaocrJ4.//bf4GQPdjKwirZ4SpK2pvXy', 'test@aletho.nl', '1', '0', '0', current_timestamp(), current_timestamp(), '1');

-- regular default test assen_admin
INSERT INTO `users` (`id`, `name`, `password`, `email`, `is_loaner`, `is_office_admin`, `is_global_admin`, `create_at`, `updated_at`, `active`)
VALUES (NULL, 'assen_admin', '$2y$10$o3P95WU2xrKRfYUye4.DbOaocrJ4.//bf4GQPdjKwirZ4SpK2pvXy', 'test@aletho.nl', '0', '1', '0', current_timestamp(), current_timestamp(), '1');

-- regular default test groningen_admin
INSERT INTO `users` (`id`, `name`, `password`, `email`, `is_loaner`, `is_office_admin`, `is_global_admin`, `create_at`, `updated_at`, `active`)
VALUES (NULL, 'groningen_admin', '$2y$10$o3P95WU2xrKRfYUye4.DbOaocrJ4.//bf4GQPdjKwirZ4SpK2pvXy', 'test@aletho.nl', '0', '1', '0', current_timestamp(), current_timestamp(), '1');

-- regular default test global_admin
INSERT INTO `users` (`id`, `name`, `password`, `email`, `is_loaner`, `is_office_admin`, `is_global_admin`, `create_at`, `updated_at`, `active`)
VALUES (NULL, 'global_admin', '$2y$10$o3P95WU2xrKRfYUye4.DbOaocrJ4.//bf4GQPdjKwirZ4SpK2pvXy', 'test@aletho.nl', '0', '0', '1', current_timestamp(), current_timestamp(), '1');

-- regular default test office_admin
INSERT INTO `users` (`id`, `name`, `password`, `email`, `is_loaner`, `is_office_admin`, `is_global_admin`, `create_at`, `updated_at`, `active`)
VALUES (NULL, 'office_admin', '$2y$10$o3P95WU2xrKRfYUye4.DbOaocrJ4.//bf4GQPdjKwirZ4SpK2pvXy', 'test@aletho.nl', '0', '1', '0', current_timestamp(), current_timestamp(), '1');