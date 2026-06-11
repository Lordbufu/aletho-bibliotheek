CREATE TABLE book_loaners (
    `bl_id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Book loaner index',
    `book_id` INT(11) UNSIGNED NOT NULL COMMENT 'Book id link',
    `loaner_id` INT(11) UNSIGNED NOT NULL COMMENT 'Loaner id link',
    `status_id` INT(11) UNSIGNED NOT NULL COMMENT 'Status id link',
    `start_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Loan start date',
    `end_at` TIMESTAMP NULL COMMENT 'Loan end date',
    `is_active` BOOLEAN DEFAULT TRUE COMMENT 'Book loan still active',
    KEY `idx_book` (`book_id`),
    KEY `idx_loaner` (`loaner_id`),
    KEY `idx_status` (`status_id`),
    FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (`loaner_id`) REFERENCES `loaners` (`loaner_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (`status_id`) REFERENCES `status` (`status_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT 'Book loaners links and meta data';