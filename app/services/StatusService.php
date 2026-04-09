<?php
namespace App\services;

use App\App;
use App\Libs\Context\StatusContext;

class StatusService {
    private \App\Libs\StatusRepo $statuses;

    public function __construct() {
        $this->statuses = new \App\Libs\StatusRepo();
    }

    /** Facade: A simple get all function, best used at a helper i think ??¿¿ */
    public function getAll(): array {
        return $this->statuses->getAll();
    }

    /** Facade: Get raw status row data by id */
    public function getStatusRowById(int $id): ?array {
        return $this->statuses->getStatusRowById($id);
    }

    /** Facade: Get StatusContext based on id ) */
    public function getStatusById(int $statusId): ?StatusContext {
        return $this->statuses->getStatusById($statusId);
    }

    /** Facade: Get all active statuses based on book id(s) */
    public function getActiveStatuses(array $bookIds): ?array {
        return $this->statuses->getActiveStatuses($bookIds);
    }

    /** API: Request formatted status data for the frontend */
    public function getAllFormatted(): array {
        $statuses = $this->getAll();

        return array_map(function ($status) {
            return [
                'id'   => $status['id'],
                'type' => $status['type']
            ];
        }, $statuses);
    }

    /** API: Get status types we can edit */
        // TODO: Review the name and function of this
    public function getEditableStatuses(): array {
        $all = $this->statuses->getAll();

        $filtered = array_filter($all, function ($s) {
            $type = strtolower($s['type']);
            return !in_array($type, ['aanwezig', 'overdatum']);
        });

        return array_values($filtered);
    }

    /** API: Update the provided data for the edited status type */
        // TODO: Review why this isnt id based, but rather seems to be type/string based ?
    public function updatePeriod(array $data): bool {
        try {
            $this->statuses->updatePeriod(
                $data['status_type'],
                $data['period_length'],
                $data['reminder_day'],
                $data['overdue_day']
            );
            return true;
        } catch (\Throwable $t) {
            error_log("[StatusService] " . $t->getMessage());
            return false;
        }
    }
}
