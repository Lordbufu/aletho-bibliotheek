INSERT INTO `status_transition`
    (`from_status_id`, `to_status_id`, `noti_id`, `context_requirements`)
VALUES
    -- Uitlenen flows
    ('1', '2', '1', '{ "needsBook": true, "needsLoaner": true, "needsLoan": true, "needsDueDate": true, "needsBookStatus": true, "needsNotificationType": true }'),
    ('3', '2', '3', '{ "needsBook": true, "needsLoaner": true, "needsLoan": true, "needsDueDate": true, "needsBookStatus": true, "needsNotificationType": true }'),
    ('1', '6', '5', '{ "needsBook": true, "needsLoaner": true, "needsOffice": true, "needsTransport": true, "needsBookStatus": true, "needsNotificationType": true }'),
    -- Transport flows
    ('7', '9', '5', '{ "needsBook": true, "needsLoaner": true, "needsOffice": true, "needsTransport": true, "needsBookStatus": true, "needsNotificationType": true }'),
    ('8', '9', '5', '{ "needsBook": true, "needsLoaner": true, "needsLoan": true, "needsOffice": true, "needsTransport": true, "needsBookStatus": true, "needsNotificationType": true }'),
    -- Ligt Klaar flows
    ('9', '7', '2', '{ "needsBook": true, "needsLoaner": true, "needsLoan": true, "needsBookStatus": true, "needsNotificationType": true }'),
    ('8', '7', '2', '{ "needsBook": true, "needsLoaner": true, "needsLoan": true, "needsBookStatus": true, "needsNotificationType": true }'),
    -- Aanwezig flows
    ('8', '1', NULL, '{ "needsBookStatus": true, "needsLoan": true }'),
    ('9', '1', NULL, '{ "needsBookStatus": true, "needsLoan": true }'),
    -- Overdatum flows
    ('2', '5', '7', '{ "needsLoan": true, "needsLoaner": true, "needsBookStatus": true, "needsNotificationType": true }'),
    -- Ingeleverd flows
    ('2', '1', NULL, '{ "needsBookStatus": true, "needsLoan": true }'),
    ('2', '6', '5', '{ "needsBook": true, "needsLoaner": true, "needsLoan": true, "needsOffice": true, "needsTransport": true, "needsBookStatus": true, "needsNotificationType": true }'),
    ('2', '3', '2', '{ "needsBook": true, "needsLoaner": true, "needsLoan": true, "needsReservation": true, "needsBookStatus": true, "needsNotificationType": true }'),
    ('5', '1', NULL, '{ "needsBookStatus": true, "needsLoan": true }'),
    ('5', '6', '5', '{ "needsBook": true, "needsLoaner": true, "needsLoan": true, "needsOffice": true, "needsReservation": true, "needsTransport": true, "needsBookStatus": true, "needsNotificationType": true }'),
    ('5', '3', '2', '{ "needsBook": true, "needsLoaner": true, "needsLoan": true, "needsBookStatus": true, "needsNotificationType": true }'),
    -- Aangekomen flows
    ('6', '3', '2', '{ "needsBook": true, "needsLoaner": true, "needsLoan": true, "needsReservation": true, "needsBookStatus": true, "needsNotificationType": true }'),
    ('6', '1', NULL, '{ "needsBookStatus": true, "needsLoan": true }');