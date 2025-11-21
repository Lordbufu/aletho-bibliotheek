-- Test books
INSERT INTO `books` (`id`, `title`, `office_id`, `active`) VALUES
    (NULL, 'test book 001', '1', '1'),
    (NULL, 'test book 002', '2', '1'),
    (NULL, 'test book 003', '1', '1'),
    (NULL, 'test book 004', '2', '1'),
    (NULL, 'test book 005', '1', '1'),
    (NULL, 'test book 006', '2', '1');

-- Test writers
INSERT INTO `writers` (`id`, `name`) VALUES
    (NULL, 'Test Writer 001'),
    (NULL, 'Test Writer 002'),
    (NULL, 'Test Writer 003');

-- Test loaners
INSERT INTO `loaners` (`id`, `name`, `email`, `office_id`, `active`) VALUES
    (NULL, 'test loaner 001', 'test.loaner001@mail.com', '1', '1'),
    (NULL, 'test loaner 002', 'test.loaner002@mail.com', '2', '1');

-- Test book-genres
INSERT INTO `book_genre` (`book_id`, `genre_id`) VALUES
    ('1', '1'),
    ('2', '2'),
    ('3', '3'),
    ('4', '4'),
    ('5', '5'),
    ('6', '6');

-- Test book-writers
INSERT INTO `book_writers` (`book_id`, `writer_id`) VALUES
    ('1', '1'),
    ('2', '2'),
    ('3', '3'),
    ('4', '2'),
    ('5', '1'),
    ('6', '3'),
    ('1', '3');

-- Test book-status
INSERT INTO `book_status` (`book_id`, `status_id`, `send_mail`, `active`) VALUES
    (1, 1, 0, 1),
    (2, 2, 0, 1),
    (3, 3, 0, 1),
    (4, 4, 0, 1),
    (5, 5, 0, 1),
    (6, 4, 0, 1);