-- regular default test user
INSERT INTO `users`
    (`id`, `name`, `password`, `email`, `is_loaner`, `is_office_admin`, `is_global_admin`, `create_at`, `updated_at`, `active`)
VALUES
    (NULL, 'user', 'test 001', 'test@aletho.nl', '1', '0', '0', current_timestamp(), current_timestamp(), '1');
-- regular default test office_admin
INSERT INTO `users`
    (`id`, `name`, `password`, `email`, `is_loaner`, `is_office_admin`, `is_global_admin`, `create_at`, `updated_at`, `active`)
VALUES
    (NULL, 'office_admin', 'test 001', 'test@aletho.nl', '0', '1', '0', current_timestamp(), current_timestamp(), '1');
-- regular default test global_admin
INSERT INTO `users`
    (`id`, `name`, `password`, `email`, `is_loaner`, `is_office_admin`, `is_global_admin`, `create_at`, `updated_at`, `active`)
VALUES
    (NULL, 'global_admin', 'test 001', 'test@aletho.nl', '0', '0', '1', current_timestamp(), current_timestamp(), '1');