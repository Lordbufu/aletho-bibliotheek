CREATE TABLE `notifications` (
    `noti_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Notification index',
    `noti_name` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Notification name',
    `noti_templ_id` INT(11) UNSIGNED NOT NULL COMMENT 'Notification template id link',
    `noti_req_tokens` JSON NOT NULL COMMENT 'Notification required template tokens',
    `noti_opt_tokens` JSON NULL DEFAULT NULL COMMENT 'Notification optional template tokens',
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Notification active',
    PRIMARY KEY (`noti_id`),
    FOREIGN KEY (`noti_templ_id`) REFERENCES `mail_templates`(`templ_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT 'Primary notification storage';