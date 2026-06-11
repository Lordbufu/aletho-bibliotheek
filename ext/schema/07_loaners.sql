CREATE TABLE `loaners` (
    `loaner_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Loaner index',
    `loaner_name` VARCHAR(50) NOT NULL COMMENT 'Loaner display name',
    `loaner_email` VARCHAR(255) NOT NULL COMMENT 'Loaner contact email',
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Loaner active flag',
    PRIMARY KEY (`loaner_id`),
    UNIQUE KEY (`loaner_email`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = 'Primary loaner storage';