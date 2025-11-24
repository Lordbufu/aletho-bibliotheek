-- Database Schema for Mail Template Management
CREATE TABLE `mail_templates` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT,
    `subject` VARCHAR(255) NOT NULL,
    `from_mail` VARCHAR(50) NOT NULL,
    `from_name` VARCHAR(50) NOT NULL,
    `body_html` TEXT NOT NULL,
    `body_text` TEXT DEFAULT NULL,
    `active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;