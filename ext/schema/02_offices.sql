-- Database Schema for Office Management
CREATE TABLE `offices` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT,
    `name` VARCHAR(25) NOT NULL,
    `active` BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;