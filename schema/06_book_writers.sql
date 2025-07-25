-- Database Schema for linking books <-> writers
CREATE TABLE `book_writer`(
    `book_id` INT(11) UNSIGNED NOT NULL,
    `writer_id` INT(11) UNSIGNED NOT NULL,
    PRIMARY KEY(`book_id`, `writer_id`),
    FOREIGN KEY(`book_id`) REFERENCES `books`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(`writer_id`) REFERENCES `writers`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;