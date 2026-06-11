CREATE TABLE `writers`(
    `writer_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Writer index',
    `writer_name` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Full display name of the writer',
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Writer active flag',
    PRIMARY KEY(`writer_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT 'Primary writer storage';