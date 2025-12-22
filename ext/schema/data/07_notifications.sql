-- Insert default notification data
INSERT INTO `notifications` (`type`, `active`, `template_id`) VALUES
    ('loan_confirm', '1', '1'),
    ('pickup_ready_confirm', '1', '2'),
    ('pickup_confirm', '1', '3'),
    ('return_reminder', '1', '4'),
    ('transport_request', '1', '5'),
    ('transp_req_auto', '1', '5'),
    ('reserv_confirm', '1', '6'),
    ('reserv_confirm_auto', '1', '6'),
    ('overdue_reminder_user', '1', '7')
    ('overdue_reminder_admin', '1', '7');