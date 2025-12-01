<?php
namespace App\Service;

use App\App;

/** Status Service file, basicaly a facade for the SatusRepo. */
class StatusService {
    protected \App\Libs\StatusRepo  $statuses;
    protected \App\Database         $db;

    public function __construct() {
        try {
            $this->statuses = App::getLibrary('status');
            $this->db       = App::getService('database');
        } catch (\Throwable $t) {
            throw $t;
        }
    }

    public function getAllStatuses($tag = null): array {
        return $this->statuses->getAllStatuses($tag);
    }

    public function getBookStatus(int $bookId): ?string {
        return $this->statuses->getBookStatus($bookId);
    }
    
    public function getStatusById(int $statusId): ?array {
        return $this->statuses->getStatusById($statusId);
    }

    public function getBookDueDate(int $bookId): ?string {
        return $this->statuses->getBookDueDate($bookId);
    }

    public function updateStatusPeriod(int $statusId, ?int $periode_length, ?int $reminder_day, ?int $overdue_day): bool {
        return $this->statuses->updateStatusPeriod($statusId, $periode_length, $reminder_day, $overdue_day);
    }

    public function setBookStatus(int $bookId, int $statusId): bool {
        return $this->statuses->setBookStatus($bookId, $statusId);
    }

    public function updateBookStatusContext(int $bookStatusId, array $actionContext = [], ?bool $finished = null): bool {
        return $this->statuses->updateBookStatusContext($bookStatusId, $actionContext, $finished);
    }
}