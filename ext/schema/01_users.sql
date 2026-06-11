CREATE TABLE `users` (
    `user_id` INT(11) UNSIGNED  NOT NULL AUTO_INCREMENT COMMENT 'Primary index',
    `user_name` VARCHAR(255) NOT NULL COMMENT 'Display/Login name',
    `user_email` VARCHAR(255) NOT NULL COMMENT 'Unique mail address',
    `user_password` VARCHAR(255) NOT NULL COMMENT 'Hashed password',
    `user_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Record created at',
    `user_updated` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Record updated at',
    `permission_flags` JSON NOT NULL COMMENT 'JSON array of permission flags',
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'User is active flag',
    PRIMARY KEY (`user_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = 'User primary storage';