-- Test data for book status
INSERT INTO
    `book_status`
        (`book_id`, `stat_id`, `meta_id`, `loaner_id`, `current_location`, `start_date`, `send_mail`)
    VALUES
        ('1', '16', '13', '1', '1', current_timestamp(), '0'),
        ('1', '16', '16', '2', '2', current_timestamp(), '0'),
        ('3', '17', '14', '2', '1', current_timestamp(), '0'),
        ('4', '19', '17', '1', '2', current_timestamp(), '0'),
        ('5', '20', '16', '2', '2', current_timestamp(), '0'),
        ('6', '18', '18', '1', '1', current_timestamp(), '0');