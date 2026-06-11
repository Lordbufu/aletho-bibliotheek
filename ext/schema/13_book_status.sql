CREATE TABLE `book_status` (
    `bs_id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Book status index',
    `book_id`           INT UNSIGNED NOT NULL COMMENT 'Book id link',
    `status_id`         INT UNSIGNED NOT NULL COMMENT 'Status id link',
    `noti_sent`         BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Notification send flag',
    `noti_sent_at`      DATETIME DEFAULT NULL COMMENT 'Notification send date',
    `bs_created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Book status created at',
    `is_active`         BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Book status still active',
    `action_name`       VARCHAR(50) DEFAULT NULL COMMENT 'Action name',
    `action_token`      VARCHAR(255) DEFAULT NULL COMMENT 'Action token',
    `action_expires`    TIMESTAMP NULL DEFAULT NULL COMMENT 'Action expire date',
    `action_used`       BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Action used flag',
    `action_complete`   BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Action resolved/finished',
    KEY (`status_id`),
    KEY `idx_book_active` (`book_id`, `is_active`),
    CONSTRAINT `fk_book` FOREIGN KEY (`book_id`) REFERENCES books(`book_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_status` FOREIGN KEY (`status_id`) REFERENCES status(`status_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT 'Book status meta data and links';