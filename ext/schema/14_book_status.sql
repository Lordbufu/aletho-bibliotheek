-- Database Schema for linking books <-> status (defaulting to 'aanwezig')
CREATE TABLE `book_status`(
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `book_id` INT UNSIGNED NOT NULL,
    `stat_id` INT UNSIGNED NOT NULL,
    `meta_id` INT UNSIGNED NULL DEFAULT NULL,
    `loaner_id` INT UNSIGNED NULL DEFAULT NULL,
    `current_location` INT(11) UNSIGNED NULL DEFAULT NULL,
    `start_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `send_mail` TINYINT(1) NOT NULL DEFAULT FALSE,
    `active` TINYINT(1) NOT NULL DEFAULT 1,

    KEY (`book_id`),
    KEY (`stat_id`),
    KEY (`meta_id`),
    KEY (`loaner_id`),
    KEY (`current_location`),

  CONSTRAINT `fk_book` FOREIGN KEY (`book_id`) REFERENCES books(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_status` FOREIGN KEY (`stat_id`) REFERENCES status(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_meta` FOREIGN KEY (`meta_id`) REFERENCES book_sta_meta(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_loaner` FOREIGN KEY (`loaner_id`) REFERENCES loaners(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_location` FOREIGN KEY (`current_location`) REFERENCES offices(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;