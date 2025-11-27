<?php
namespace App\Service;

use App\App;

class StatusService {
    protected \App\Libraries    $libs;
    protected \App\Database     $db;

    public function __construct() {
        try {
            $this->libs = App::getLibraries();
            $this->db   = App::getService('database');
        } catch (\Throwable $t) {
            throw $t;
        }
    }

    /* */
    public function getAllStatuses($tag = null): array {
        return $this->status->getAllStatuses($tag);
    }

    /* */
    public function getBookStatus(int $bookId): ?string {
        return $this->status->getBookStatus($bookId);
    }

    /* */
    public function getBookDueDate(int $bookId): ?string {
        return $this->status->getBookDueDate($bookId);
    }

    /*  Update status period settings */
    public function updateStatusPeriod(int $statusId, ?int $periode_length, ?int $reminder_day, ?int $overdue_day): bool {
        return $this->status->updateStatusPeriod($statusId, $periode_length, $reminder_day, $overdue_day);
    }

    /* */
    public function setBookStatus(int $bookId, int $statusId, ?int $metaId = null, ?int $loanerId = null, ?int $locationId = null, bool $sendMail = false): bool {
        return $this->status->setBookStatus($bookId, $statusId, $metaId, $loanerId, $locationId, $sendMail);
    }
}