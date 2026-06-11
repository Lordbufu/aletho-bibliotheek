CREATE TABLE `book_reservations` (
    `br_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Book reservation index',
    `book_id` INT(11) UNSIGNED NOT NULL COMMENT 'Book id link',
    `loaner_id` INT(11) UNSIGNED NOT NULL COMMENT 'Loaner id link',
    `loc_id` INT(11) UNSIGNED NOT NULL COMMENT 'Location id link',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Reservation creation date',
    `expires_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Reservation expire date',
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Reservation active',
    PRIMARY KEY (`br_id`),
    KEY `idx_location` (`loc_id`),
    UNIQUE KEY `uniq_active_reservation` (`book_id`, `loaner_id`, `is_active`),
    CONSTRAINT `fk_reservation_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_reservation_loaner` FOREIGN KEY (`loaner_id`) REFERENCES `loaners` (`loaner_id`) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_reservation_location` FOREIGN KEY (`loc_id`) REFERENCES `locations` (`loc_id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT 'Book reservation link and meta data';