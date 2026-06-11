CREATE TABLE `status_transition`(
    `st_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Status transition index',
    `from_status_id` INT(11) UNSIGNED NOT NULL COMMENT 'From status link',
    `to_status_id` INT(11) UNSIGNED NOT NULL COMMENT 'To status link',
    `noti_id` INT(11) UNSIGNED DEFAULT NULL COMMENT 'Notification id',
    `context_requirements` JSON NOT NULL COMMENT 'Status transition engine context',
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Status transition active',
    PRIMARY KEY(`st_id`),
    KEY `idx_notifications`(`noti_id`),
    UNIQUE KEY `uniq_transition` (`from_status_id`, `to_status_id`),
    CONSTRAINT `fk_transition_from_status` FOREIGN KEY(`from_status_id`) REFERENCES `status`(`status_id`) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_transition_to_status` FOREIGN KEY(`to_status_id`) REFERENCES `status`(`status_id`) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_transition_notifications` FOREIGN KEY(`noti_id`) REFERENCES `notifications`(`noti_id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci  COMMENT 'Status transition context storage';