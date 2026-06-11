CREATE TABLE `book_writers`(
    `book_id` INT(11) UNSIGNED NOT NULL COMMENT 'Book id link',
    `writer_id` INT(11) UNSIGNED NOT NULL COMMENT 'Writer id link',
    PRIMARY KEY(`book_id`, `writer_id`),
    FOREIGN KEY(`book_id`) REFERENCES `books`(`book_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY(`writer_id`) REFERENCES `writers`(`writer_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT 'Book writers links';