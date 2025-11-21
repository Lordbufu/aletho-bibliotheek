-- book loaner link table, to seperate concerns and declutter php logic
CREATE TABLE book_loaner (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    book_id INT UNSIGNED NOT NULL UNIQUE,
    loaner_id INT UNSIGNED NOT NULL,
    status_id INT UNSIGNED,
    start_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    end_date TIMESTAMP NULL,
    active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (book_id) REFERENCES books(id),
    FOREIGN KEY (loaner_id) REFERENCES loaners(id),
    FOREIGN KEY (status_id) REFERENCES status(id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;