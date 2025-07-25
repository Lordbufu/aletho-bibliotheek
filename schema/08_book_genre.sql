-- Database Schema for linking books <-> genres
CREATE TABLE `biblioapp`.`book_genre`(
    `book_id` INT(11) UNSIGNED NOT NULL,
    `genre_id` INT(11) UNSIGNED NOT NULL,
    PRIMARY KEY(`book_id`, `genre_id`),
    FOREIGN KEY(`book_id`) REFERENCES `books`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(`genre_id`) REFERENCES `genres`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;