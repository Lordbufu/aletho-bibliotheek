# Aletho Bibliotheek

Aletho Bibliotheek is a simple PHP library management application. It lets you catalog books, manage borrowers, and track loans through a clean, minimal interface. The project is structured as a library-style app, using Composer for dependencies and PHPUnit for automated testing.

Somethings are still work in progress, and will be finalized at a later point in time.

---

## Features

- Add, edit, and delete book records  
- Manage borrower profiles and loan statuses  
- Search and filter books by title, author, or ISBN  
- Email notifications for overdue loans (via mailer library)  
- Automated test suite for core functionality  

---

## Tech Stack

- PHP (7.4 or higher)  
- Composer for dependency management  
- PHPUnit for unit and integration tests  
- PHPMailer (or similar) for sending emails  
- PSR-4 autoloading for project classes  

---

## Requirements

- PHP 7.4 or newer  
- Composer (latest stable)  
- A web server or PHP’s built-in server for local development  
- SQLite, MySQL, or PostgreSQL (configurable in `.env`)  

---

## Installation

1. Clone the repository  
   ```bash
   git clone https://github.com/Lordbufu/aletho-bibliotheek.git
   cd aletho-bibliotheek
   ```  

2. Install PHP dependencies  
   ```bash
   composer install
   ```  

3. Copy the example environment file and configure  
   ```bash
   cp .env.example .env
   ```  
   Update database credentials and mailer settings in `.env`.  

4. Run database setup (migrations or SQL seed)  
   ```bash
   # Example if you have migration scripts
   php bin/console migrate
   ```  

5. Start the local development server  
   ```bash
   php -S localhost:8000 -t public
   ```  
   Visit `http://localhost:8000` in your browser.  

---

## Configuration

- `.env` holds sensitive settings (database DSN, mail credentials).  
- Do not commit `.env` to Git; it’s excluded via `.gitignore`.  
- Use `.env.example` as a template for new environments.

---

## Running Tests

Execute the PHPUnit suite to verify functionality:

```bash
vendor/bin/phpunit
```

Ensure you’ve configured a test database or in-memory driver in `phpunit.xml`.  

---

## License

This project is released under the MIT License. See `LICENSE` for full details.

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

---

## Contact

For questions or feedback, open an issue or reach out to the maintainer at notarealadress@whatever.com.  

---  