CREATE TABLE `user_locations` (
    `loc_id` INT(11) UNSIGNED NOT NULL,
    `user_id` INT(11) UNSIGNED NOT NULL,
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Location link still active or not',
    PRIMARY KEY (`loc_id`, `user_id`),
    FOREIGN KEY (`loc_id`) REFERENCES `locations`(`loc_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT 'Primary storage for user locations';