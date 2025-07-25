-- Database Schema for linking books <-> status
CREATE TABLE `book_status`(
    `book_id` INT(11) UNSIGNED NOT NULL,
    `stat_id` INT(11) UNSIGNED NOT NULL,
    `meta_id` INT(11) UNSIGNED NOT NULL,
    `loaner_id` INT(11) UNSIGNED NOT NULL,
    `current_location` INT(11) UNSIGNED NOT NULL,
    `start_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `send_mail` BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY(
        `book_id`,
        `stat_id`,
        `meta_id`,
        `loaner_id`
    ),
    FOREIGN KEY(`book_id`) REFERENCES `books`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(`stat_id`) REFERENCES `status`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(`meta_id`) REFERENCES `book_sta_meta`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(`loaner_id`) REFERENCES `loaners`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(`current_location`) REFERENCES `offices`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;