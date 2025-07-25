-- Link table for user <-> offices
CREATE TABLE `biblioapp`.`user_office` (
    `user_id` INT(11) UNSIGNED NOT NULL,
    `office_id` INT(11) UNSIGNED NOT NULL,
    `active` BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (`user_id`, `office_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`office_id`) REFERENCES `offices`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB
DEFAULT CHARSET = utf8mb4
COLLATE = utf8mb4_general_ci;