-- Database Schema for linking books <-> status (defaulting to 'aanwezig')
CREATE TABLE `book_status` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `book_id` INT UNSIGNED NOT NULL,
    `status_id` INT UNSIGNED NOT NULL,
    `action_type` VARCHAR(25) DEFAULT NULL,
    `action_token` VARCHAR(255) DEFAULT NULL,
    `token_expires` TIMESTAMP NULL DEFAULT NULL,
    `token_used` BOOLEAN NOT NULL DEFAULT FALSE,
    `finished` BOOLEAN NOT NULL DEFAULT FALSE,
    `active` TINYINT(1) NOT NULL DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    KEY (`book_id`),
    KEY (`status_id`),

    CONSTRAINT `fk_book` FOREIGN KEY (`book_id`) REFERENCES books(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_status` FOREIGN KEY (`status_id`) REFERENCES status(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;