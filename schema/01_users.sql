-- Database Schema for User Management
CREATE TABLE `biblioapp`.`users` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT,
    `name` VARCHAR(25) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(50) NULL DEFAULT NULL,
    `is_loaner` BOOLEAN NOT NULL DEFAULT TRUE,
    `is_office_admin` BOOLEAN NOT NULL DEFAULT FALSE,
    `is_global_admin` BOOLEAN NOT NULL DEFAULT FALSE,
    `create_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `active` BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;