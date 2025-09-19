-- Test data for book status meta (not correctly filled in yet)
INSERT INTO `book_sta_meta`
    (`id`, `noti_id`, `action_type`, `action_token`, `token_expires`, `token_used`, `finished`)
VALUES
    (NULL, '1', 'Bevestiging01', '123456', '2027-09-01 11:01:36', '0', '1'),
    (NULL, '2', 'Herinering02', '123457', '2027-09-01 11:01:36', '0', '1'),
    (NULL, '3', 'Transport verzoek03', '123458', '2027-09-01 11:01:36', '0', '1'),
    (NULL, '1', 'Bevestiging04', '123459', '2027-09-01 11:01:36', '0', '1'),
    (NULL, '2', 'Herinering05', '123450', '2027-09-01 11:01:36', '0', '1'),
    (NULL, '3', 'Transport verzoek06', '123451', '2027-09-01 11:01:36', '0', '1');