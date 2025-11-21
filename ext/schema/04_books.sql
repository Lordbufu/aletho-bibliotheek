-- Database Schema for management
CREATE TABLE `books`(
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(100) NOT NULL,
    `office_id` INT(11) UNSIGNED NOT NULL,
    `cur_office` INT(11) UNSIGNED NOT NULL,
    `active` BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY(`id`),
    FOREIGN KEY(`office_id`) REFERENCES `offices`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;