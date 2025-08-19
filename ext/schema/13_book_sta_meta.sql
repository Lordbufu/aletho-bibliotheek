-- Database Schema for book meta data management
CREATE TABLE `book_sta_meta`(
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `noti_id` INT(11) UNSIGNED NOT NULL,
    `action_type` VARCHAR(25) UNIQUE DEFAULT NULL,
    `action_token` VARCHAR(255) UNIQUE DEFAULT NULL,
    `token_expires` TIMESTAMP NULL DEFAULT NULL,
    `token_used` BOOLEAN NOT NULL DEFAULT FALSE,
    `finished` BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY(`id`),
    FOREIGN KEY(`noti_id`) REFERENCES `notifications`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;