-- Database Schema for Mail Template Management
CREATE TABLE `biblioapp`.`mail_templates` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT,
    `mail_type` VARCHAR(50) NOT NULL,
    `from_mail` VARCHAR(50) NOT NULL,
    `from_name` VARCHAR(50) NOT NULL,
    `header_string` TEXT NOT NULL,
    `body_text_1` TEXT NOT NULL,
    `body_text_2` TEXT DEFAULT NULL,
    `action_intro` TEXT NOT NULL,
    `footer_outro` TEXT NOT NULL,
    `footer_notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;