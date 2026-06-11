INSERT INTO `notifications`
    (`noti_name`, `noti_templ_id`, `noti_req_tokens`, `noti_opt_tokens`)
VALUES
    ('loan_confirm', '1', '[":book_name", ":loaner_name", ":due_date"]', NULL),
    ('pickup_ready_confirm', '2', '[":loaner_name", ":book_name", ":office"]', NULL),
    ('pickup_confirm', '3', '[":loaner_name", ":book_name", ":due_date"]', NULL),
    ('return_reminder', '4', '[":loaner_name", ":book_name", ":due_date"]', '[":action_block"]'),
    ('transport_request', '5', '[":admin_name", ":loaner_name", ":book_name", ":office"]', NULL),
    ('reserv_confirm', '6', '[":loaner_name", ":book_name"]', NULL),
    ('overdue_reminder_user', '7', '[":loaner_name", ":book_name"]', NULL),
    ('overdue_reminder_admin', '7', '[":admin_name", ":loaner_name", ":book_name"]', NULL);