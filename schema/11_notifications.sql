-- Database Schema for notification management
CREATE TABLE `notifications`(
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `type` VARCHAR(25) NOT NULL UNIQUE,
    `active` BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY(`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;