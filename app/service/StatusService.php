<?php
namespace App\Service;

use App\App;

/** Status Service file, basicaly a facade for the SatusRepo. */
class StatusService {
    protected \App\Libs\StatusRepo  $statuses;
    protected array                 $eventStatusMap;

    public function __construct(array $config) {
        try {
            $this->statuses         = App::getLibrary('status');
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

    public function disableBookStatus(int $bookId): null {
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

    public function updateStatusLinks(int $bookStatusId, int $notifId): int {
        return $this->statuses->updateStatusLinks($bookStatusId, $notifId);
    }

    public function setBookStatus(int $bookId, int $statusId): ?int {
        return $this->statuses->setBookStatus($bookId, $statusId);
    }
    
    public function updateBookStatus(int $bookId, int $requestedStatusId, bool $transport, string $trigger): array {
        return $this->statuses->updateBookStatus($bookId, $requestedStatusId, $transport, $trigger);
    }

    public function getNotificationsForStatus(int $statusId): array {
        return $this->statuses->getNotificationsForStatus($statusId);
    }

    public function updateBookStatusContext(int $bookStatusId, array $actionContext = [], ?bool $finished = null): bool {
        return $this->statuses->updateBookStatusContext($bookStatusId, $actionContext, $finished);
    }

    /** API\Facade: Find the correct event key, in the pre-defined statusMap */
    public function findEventKey(array $eventStatusMap, int $finalStatusId, ?int $oldStatus = null, ?string $currentTrigger = null): ?string {
        if (empty($eventStatusMap)) {
            $eventStatusMap = $this->eventStatusMap;
        }

        return $this->statuses->findEventKey($eventStatusMap, $finalStatusId, $oldStatus, $currentTrigger);
    }

    /** API: Local logic operations */
    public function linkEventIfNeeded(array $statusUpdate, int $requestedStatusId, int $oldStatus, string $trigger, array $requestStatus): ?int {
        if ($statusUpdate['finalStatusId'] === 1) {
            return null;
        }

        $finalStatusId  = $statusUpdate['finalStatusId'];
        $recordId       = $statusUpdate['record_id'];

        $eventKey = $this->findEventKey($this->eventStatusMap, $finalStatusId, $oldStatus, $trigger);
        if (!$eventKey) {
            return null;
        }

        $eventKeyId = App::getLibrary('notification')->getNotiIdByType($eventKey);

        if ($eventKeyId && !empty($requestStatus)) {
            $this->statuses->setStatusEvent($recordId, $finalStatusId, $eventKeyId);
        }
        return $eventKeyId;
    }
}