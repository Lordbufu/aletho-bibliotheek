-- Insert default notification data
INSERT INTO `notifications` (`type`, `active`, `template_id`) VALUES
    ('loan_confirm', '1', '1'),
    ('pickup_ready_confirm', '1', '2'),
    ('pickup_confirm', '1', '3'),
    ('return_reminder', '1', '4'),
    ('transport_request', '1', '5'),
    ('reserv_confirm', '1', '6'),
    ('overdue_reminder', '1', '7');