CREATE TABLE `book_genres`(
    `book_id` INT(11) UNSIGNED NOT NULL COMMENT 'Book id link',
    `genre_id` INT(11) UNSIGNED NOT NULL COMMENT 'Genre id link',
    PRIMARY KEY(`book_id`, `genre_id`),
    FOREIGN KEY(`book_id`) REFERENCES `books`(`book_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY(`genre_id`) REFERENCES `genres`(`genre_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT 'Book genre links';