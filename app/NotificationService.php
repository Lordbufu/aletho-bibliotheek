<?php
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\{PHPMailer, Exception};

class NotificationService {
    public function notifyUser(int $userId, string $event, array $context): void {
        //...
    }

    public function notifyOffice(int $officeId, string $event, array $context): void {
        //...
    }

    // overdue, reminders, etc.
    public function processCronEvents(): void {
        //...
    }
}

// 1. Laad environment (phpdotenv) en maak DB-verbinding (PDO)
// 2. Haal alle Status_Noti regels op met bijbehorende status- en notificatie­data
// 3. Loop per regel:
//    a. Bepaal welke Book_stat records in aanmerking komen (reminder_day of overdue_day bereikt)
//    b. Voor elk record: 
//       - Laad mailtemplate (Mail_templates)
//       - Vul dynamische velden (boektitel, leendatum, gebruikersnaam, link voor verlengen, etc.)
//       - Stuur e-mail met PHPMailer
//       - Schrijf een entry in Book_sta_meta (noti_id, timestamp, eventueel token)
// 4. Log resultaten en fouten naar stdout of een logbestand

// 4. Workflow per notificatie
//    1. Selecteer alle status­notificaties met actieve reminders of overdues.
//    2. Voor iedere status­noti bepaal items waarbij
//         DATEDIFF(NOW(), start_date) = reminder_day
//         of DATEDIFF(NOW(), start_date) = overdue_day
//    3. Markeer na verzending de meta­record om dubbele mails te voorkomen.
//    4. Bij Missers (bijv. SMTP-fout) zet je een retry-veld of log je de fout voor manuele check.