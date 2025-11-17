<?php
/*  All default status data:
 *      - [id]              = status id
 *      - [type]            = status name
 *      - [periode_length]  = periode length in days
 *      - [reminder_day]    = amount of days before the period end, that a reminder should be sent
 *      - [overdue_day]     = amount of days after the period end, that a book is considered overdue
 *      - [active]          = if a status type is still active or not
 *
 *  All default book_status link table data:
 *      - [book_id]             = book id link
 *      - [stat_id]             = status id link
 *      - [meta_id]             = meta id link (optional)
 *      - [loaner_id]           = loaner id link (optional)
 *      - [current_location]    = current office id link (optional)
 *      - [start_date]          = date & time the status was started
 *      - [send_mail]           = If a mail was send for this status or not 
 *
 *  All default book_sta_meta link table data (optional data, based on other later to define variables):
 *      - [id]              = default index
 *      - [noti_id]         = notificatie id link
 *      - [action_type]     = type/name of the action
 *      - [action_token]    = unique token for this action
 *      - [token_expires]   = expire date & time for the token
 *      - [token_used]      = was the token used yes/no
 *      - [finished]        = was the action finished yes/no
 */

namespace App\Libs;

use App\{App, Database};

class StatusRepo {
    protected ?array $statuses = null;
    protected Database $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    /*  Helper: calculate due date */
    private function calculateDueDate(string $startDate, int $days): string {
        $dt = new \DateTimeImmutable($startDate);
        return $dt->add(new \DateInterval("P{$days}D"))->format('Y-m-d');
    }

    /*  Cache all statuses */
    public function getAllStatuses(): array {
        if ($this->statuses === null) {
            $this->statuses = $this->db->query()->fetchAll("SELECT * FROM status");
        }

        return $this->statuses;
    }

    /*  Get a book status for a specific book. */
    public function getBookStatus(int $bookId): ?string {
        $row = $this->db->query()->fetchOne(
            "SELECT s.type
            FROM book_status bs
            JOIN status s ON s.id = bs.stat_id
            WHERE bs.book_id = ? AND (bs.active = 1 OR bs.active IS NULL)
            ORDER BY bs.start_date DESC
            LIMIT 1",
            [$bookId]
        );

        return $row['type'] ?? null;
    }

    /*  Get the books status expire date. */
    public function getBookDueDate(int $bookId): ?string {
        $row = $this->db->query()->fetchOne(
            "SELECT bs.start_date, s.periode_length, s.id AS status_id, s.type
            FROM book_status bs
            JOIN status s ON s.id = bs.stat_id
            WHERE bs.book_id = ?
            ORDER BY bs.start_date DESC
            LIMIT 1",
            [$bookId]
        );

        if (!$row) return null;

        $startDate = $row['start_date'];
        $periodeLength = $row['periode_length'];
        $statusId = (int)$row['status_id'];
        $statusType = $row['type'];

        // Case A: Aanwezig → fallback to today
        if ($statusType === 'Aanwezig' || $periodeLength === null || $periodeLength === '') {
            if ($statusType === 'Gereserveerd') {
                // Case B: Reserved → borrow Afwezig’s periode_length
                $afwezig = $this->db->query()->value("SELECT periode_length FROM status WHERE type = 'Afwezig'");
                $periodeLength = (int)$afwezig;
            } else {
                // Default fallback: today
                return (new \DateTimeImmutable())->format('Y-m-d');
            }
        }

        return $this->calculateDueDate($startDate, (int)$periodeLength);
    }

    /*  Update status period settings */
    public function updateStatusPeriod(int $statusId, ?int $periode_length, ?int $reminder_day, ?int $overdue_day): bool {
        $sql = "UPDATE status SET periode_length = ?, reminder_day = ?, overdue_day = ? WHERE id = ?";
        return $this->db->query()->run($sql, [$periode_length, $reminder_day, $overdue_day, $statusId]) !== false;
    }

    /*  To set a book status we need (for now): */
    public function setBookStatus(int $bookId, int $statusId, ?int $metaId = null, ?int $loanerId = null, ?int $locationId = null, bool $sendMail = false): bool {
        $sqlDeactivate = "UPDATE book_status SET active = 0 WHERE book_id = ? AND active = 1";
        $this->db->query()->run($sqlDeactivate, [$bookId]);

        $sqlInsert = "
            INSERT INTO book_status (book_id, stat_id, meta_id, loaner_id, current_location, send_mail, active)
            VALUES (?, ?, ?, ?, ?, ?, 1)
        ";

        return (bool) $this->db->query()->run($sqlInsert, [
            $bookId,
            $statusId,
            $metaId,
            $loanerId,
            $locationId,
            $sendMail ? 1 : 0
        ]);
    }
}