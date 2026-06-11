CREATE TABLE `books`(
    `book_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Book index',
    `book_title` VARCHAR(150) NOT NULL COMMENT 'Book title',
    `book_home_loc` INT(11) UNSIGNED NOT NULL COMMENT 'Location where the book belongs',
    `book_cur_loc` INT(11) UNSIGNED NOT NULL COMMENT 'Location where the book is currently stored or loaned to',
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Book active flag',
    PRIMARY KEY(`book_id`),
    FOREIGN KEY (`book_home_loc`) REFERENCES `locations`(`loc_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (`book_cur_loc`) REFERENCES `locations`(`loc_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT 'Primary book storage';