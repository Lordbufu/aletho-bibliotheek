CREATE TABLE `status`(
    `status_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Status index',
    `status_name` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Status display name',
    `status_length` INT(11) UNSIGNED DEFAULT NULL COMMENT 'Status period length',
    `status_reminder` INT(11) UNSIGNED DEFAULT NULL COMMENT 'Status reminder day',
    `status_overdue` INT(11) UNSIGNED DEFAULT NULL COMMENT 'Status overdue day',
    `is_facade` BOOLEAN NOT NULL COMMENT 'Status facade tag',
    `filter_mode` ENUM('select','edit') NULL COMMENT 'Status filter tags',
    `reservation_behavior` JSON NULL COMMENT 'Status reservation behavior',
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Status active flag',
    PRIMARY KEY(`status_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT 'Primary status storage';