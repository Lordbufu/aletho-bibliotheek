CREATE TABLE `mail_templates` (
    `templ_id` INT(11) UNSIGNED AUTO_INCREMENT COMMENT 'Template index',
    `templ_subject` VARCHAR(255) NOT NULL COMMENT 'Template mail subject',
    `templ_body_html` MEDIUMTEXT NOT NULL COMMENT 'Template HTML content',
    `templ_body_text` TEXT DEFAULT NULL COMMENT 'Template plain text content',
    `templ_created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Template creation date',
    `templ_updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Template update date',
    `is_active` BOOLEAN DEFAULT TRUE COMMENT 'Template active flag',
    PRIMARY KEY (`templ_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT 'Primary template storage';