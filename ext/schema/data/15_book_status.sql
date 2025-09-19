-- Test data for book status (not correctly filled in yet)
INSERT INTO `book_status`
    (`book_id`, `stat_id`, `meta_id`, `loaner_id`, `current_location`, `start_date`, `send_mail`)
VALUES
    ('1', '1', '1', '1', '1', current_timestamp(), '0'),
    ('2', '2', '2', '2', '2', current_timestamp(), '0'),
    ('3', '3', '3', '1', '1', current_timestamp(), '0'),
    ('4', '4', '4', '2', '2', current_timestamp(), '0'),
    ('5', '5', '5', '1', '1', current_timestamp(), '0'),
    ('6', '4', '6', '2', '2', current_timestamp(), '0');