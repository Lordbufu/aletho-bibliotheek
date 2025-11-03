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

-- Test book-status-meta
INSERT INTO `book_sta_meta` (`id`, `noti_id`, `action_type`, `action_token`, `token_expires`, `token_used`, `finished`) VALUES
    (NULL, '1', 'Bevestiging01', '123456', '2027-09-01 11:01:36', '0', '1'),
    (NULL, '2', 'Herinering02', '123457', '2027-09-01 11:01:36', '0', '1'),
    (NULL, '3', 'Transport verzoek03', '123458', '2027-09-01 11:01:36', '0', '1'),
    (NULL, '1', 'Bevestiging04', '123459', '2027-09-01 11:01:36', '0', '1'),
    (NULL, '2', 'Herinering05', '123450', '2027-09-01 11:01:36', '0', '1'),
    (NULL, '3', 'Transport verzoek06', '123451', '2027-09-01 11:01:36', '0', '1');

-- Test book-status
INSERT INTO `book_status` (`book_id`, `stat_id`, `meta_id`, `loaner_id`, `current_location`, `start_date`, `send_mail`) VALUES
    ('1', '1', '1', '1', '1', current_timestamp(), '0'),
    ('2', '2', '2', '2', '2', current_timestamp(), '0'),
    ('3', '3', '3', '1', '1', current_timestamp(), '0'),
    ('4', '4', '4', '2', '2', current_timestamp(), '0'),
    ('5', '5', '5', '1', '1', current_timestamp(), '0'),
    ('6', '4', '6', '2', '2', current_timestamp(), '0');