CREATE TABLE `genres`(
    `genre_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Genre index',
    `genre_name` VARCHAR(25) NOT NULL UNIQUE COMMENT 'Full display of a readable genre label',
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Genre active flag',
    PRIMARY KEY(`genre_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT 'Primary genre storage';