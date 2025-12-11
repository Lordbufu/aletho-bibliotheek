<?php
namespace App\Service;

use App\App;

/** Status Service file, basicaly a facade for the SatusRepo. */
class StatusService {
    protected \App\Libs\StatusRepo  $statuses;
    protected \App\Database         $db;
    protected array                 $eventStatusMap;

    public function __construct(array $config) {
        try {
            $this->statuses         = App::getLibrary('status');
            $this->db               = App::getService('database');
            $this->eventStatusMap   = $config;
        } catch (\Throwable $t) {
            throw $t;
        }
    }

    /** Local API: Request `eventStatusMap` from the config. */
    public function getEventStatusMap() {
        return $this->eventStatusMap;
    }

    /** External API's, as detailed in the `StatusRepo` */
    public function getAllStatuses($tag = null): array {
        return $this->statuses->getAllStatuses($tag);
    }

    public function getStatusLinks(int $bookStatusId, int $statusId): array {
        return $this->statuses->getStatusLinks($bookStatusId, $statusId);
    }

    public function disableBookStatus(int $bookId): bool {
        return $this->statuses->disableBookStatus($bookId);
    }

    public function getBookStatus(int $bookId, string $flag = "type"): ?string {
        return $this->statuses->getBookStatus($bookId, $flag);
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

    public function updateStatusLinks(int $bookStatusId, int $notifId): bool {
        return $this->statuses->updateStatusLinks($bookStatusId, $notifId);
    }

    public function setBookStatus(int $bookId, int $statusId): ?int {
        return $this->statuses->setBookStatus($bookId, $statusId);
    }
    
    public function updateBookStatus(int $bookId, int $requestedStatusId, bool $transport): array {
        return $this->statuses->updateBookStatus($bookId, $requestedStatusId, $transport);
    }

    public function linkEventIfNeeded(array $statusResult, int $requestedStatusId, int $oldStatus, string $trigger, array $requestStatus): ?int {
        return $this->statuses->linkEventIfNeeded($statusResult, $requestedStatusId, $oldStatus, $trigger, $requestStatus);
    }

    public function getNotificationsForStatus(int $statusId): array {
        return $this->statuses->getNotificationsForStatus($statusId);
    }

    public function updateBookStatusContext(int $bookStatusId, array $actionContext = [], ?bool $finished = null): bool {
        return $this->statuses->updateBookStatusContext($bookStatusId, $actionContext, $finished);
    }
}