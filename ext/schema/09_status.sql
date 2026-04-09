-- Database Schema for status management
CREATE TABLE `status`(
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `type` VARCHAR(25) NOT NULL UNIQUE,
    `periode_length` INT(11) DEFAULT NULL,
    `reminder_day` INT(11) DEFAULT NULL,
    `overdue_day` INT(11) DEFAULT NULL,
    `active` BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY(`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;