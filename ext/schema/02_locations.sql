CREATE TABLE `locations` (
    `loc_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Location index',
    `loc_name` VARCHAR(25) NOT NULL COMMENT 'Location name',
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Location active flag',
    PRIMARY KEY (`loc_id`),
    UNIQUE KEY (`loc_name`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = 'Primary location storage';