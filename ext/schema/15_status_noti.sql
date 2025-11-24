-- Database Schema for linking status <-> notifications
CREATE TABLE `status_noti` (
    `status_id` INT(11) UNSIGNED NOT NULL,
    `notification_id` INT(11) UNSIGNED NOT NULL,
    `mail_send` BOOLEAN NOT NULL DEFAULT FALSE, -- quick flag
    `sent_at` DATETIME DEFAULT NULL,            -- timestamp of send
    PRIMARY KEY (`status_id`, `notification_id`),
    FOREIGN KEY (`status_id`) REFERENCES `status`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`notification_id`) REFERENCES `notifications`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;