-- Test books
INSERT INTO `books` (`title`, `home_office`, `cur_office`, `active`) VALUES
    ('test book 001', '1', '1', '1'),
    ('test book 002', '2', '2', '1'),
    ('test book 003', '1', '1', '1'),
    ('test book 004', '2', '2', '1'),
    ('test book 005', '1', '1', '1'),
    ('test book 006', '2', '2', '1');

-- Test writers
INSERT INTO `writers` (`name`) VALUES
    ('Test Writer 001'),
    ('Test Writer 002'),
    ('Test Writer 003');

-- Test book_writers
INSERT INTO `book_writers` (`book_id`, `writer_id`) VALUES
    ('1', '1'),
    ('2', '2'),
    ('3', '3'),
    ('4', '2'),
    ('5', '1'),
    ('6', '3'),
    ('1', '3');

-- Test book_genres
INSERT INTO `book_genre` (`book_id`, `genre_id`) VALUES
    ('1', '1'),
    ('2', '2'),
    ('3', '3'),
    ('4', '4'),
    ('5', '5'),
    ('6', '6');

-- Test book_status
INSERT INTO `book_status` (`book_id`, `status_id`, `active`) VALUES
    (1, 1, 1),
    (2, 2, 1),
    (3, 3, 1),
    (4, 4, 1),
    (5, 5, 1),
    (6, 6, 1);

-- Test loaners
INSERT INTO `loaners` (`name`, `email`, `office_id`, `active`) VALUES
    ('test loaner 001', 'test.loaner001@mail.com', '1', '1'),
    ('test loaner 002', 'test.loaner002@mail.com', '2', '1');

-- Test book_loaners
INSERT INTO `book_loaners` (`book_id`, `loaner_id`, `status_id`) VALUES
    ('1', '1', '1'),
    ('2', '2', '2'),
    ('3', '1', '3'),
    ('4', '2', '4'),
    ('5', '1', '5'),
    ('6', '2', '6');

INSERT INTO `status_noti` (`status_id`, `notification_id`, `mail_send`) VALUES
    ('1', '1', '1'),
    ('2', '2', '1'),
    ('3', '3', '1'),
    ('4', '4', '1'),
    ('5', '5', '1'),
    ('6', '6', '1');