-- Database Schema for notification management, linking it with the mail_templates as well.
CREATE TABLE `notifications` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `type` VARCHAR(25) NOT NULL UNIQUE,   -- e.g. 'loan_reminder', 'pickup_notice'
    `active` BOOLEAN NOT NULL DEFAULT TRUE,
    `template_id` INT(11) UNSIGNED NOT NULL, -- FK to mail_templates
    PRIMARY KEY (`id`),
    FOREIGN KEY (`template_id`) REFERENCES `mail_templates`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;