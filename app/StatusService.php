<?php

namespace App;

use App\App;

class StatusService {
    protected \App\Libs\StatusRepo    $status;
    protected \App\Libs\LoanersRepo   $loaners;

    public function __construct() {
        $this->status   = new \App\Libs\StatusRepo();
        $this->loaners  = new \App\Libs\LoanersRepo();
    }

    /* */
    public function getAllStatuses(): array {
        return $this->status->getAllStatuses();
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