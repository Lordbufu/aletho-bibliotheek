<?php
namespace App\services;

use App\Libs\Context\StatusContext;
use App\ViewModel\StatusViewModel;

class StatusService {
    private \App\Libs\StatusRepo $statuses;

    public function __construct() {
        $this->statuses     = new \App\Libs\StatusRepo();
    }

    // Re-factored functions
    // New function for DTO related frontend filtering.
    /** Helper: Filter dataset based on the `status`.`filter_mode` field */
    private function filterByMode(array $statuses, string $mode): array {
        return array_filter($statuses, fn($s) =>
            ($s->filter_mode === null) || ($s->filter_mode === $mode)
        );
    }

    // Re-factor status: tested and working (not used so far though)
    /** Facade: A simple get all function */
    public function getAllStatuses(string $mode = 'all'): array {
        return $this->statuses->getStatuses($mode);
    }

    // Re-factor status: tested and working
    /** Facade: A simple get active all function */
    public function getAllActiveStatuses(string $mode = 'active'): array {
        return $this->statuses->getStatuses($mode);
    }

    // Re-factor status: tested and working
    /** API: Get all editable statuses for view functions */
    public function getStatusForEdit(): array {
        $statuses = $this->filterByMode(
            $this->getAllActiveStatuses(),
            'edit'
        );

        return StatusViewModel::formatMany($statuses, 'edit');
    }

    // Re-factor status: tested and working
    /** API: Get all selectable statuses for view functions */
    public function getStatusForSelect(): array {
        $statuses = $this->filterByMode(
            $this->getAllActiveStatuses(),
            'select'
        );

        return StatusViewModel::formatMany($statuses, 'select');
    }

    // Re-factor status: tested and working
    /** API: Get status by bookId, requires bookId as array-key to return the viewmodel because of how the repo works */
    public function getStatusByBookId($bookId): StatusViewModel {
        $status = $this->statuses->getBookStatusByBookId($bookId);
        return new StatusViewModel($status[$bookId]);
    }
}

    // recycled functions no longer relevant for the new flows ?
    /** Helper: Filter status list for UI based on provided mode tag */
        // private function filterStatusList(array $all, ?string $mode) {
        //     return match ($mode) {
        //         'active' => array_values(array_filter(
        //             $all,
        //             fn(StatusContext $s) => ($s->facade ?? 0) == 1
        //         )),

        //         'edit', 'select' => array_values(array_filter(
        //             $all,
        //             fn(StatusContext $s) => ($s->filter ?? null) === $mode
        //         )),

        //         default => $all,
        //     };
        // }

    /** API: Request frontend status data, for edit/select/display reasons.
     *      $mode options = active\edit\select
     */
        // public function getUiStatuses(?string $mode = null): array {
        //     return $this->filterStatusList(
        //         $this->getAllActiveStatuses(),
        //         $mode
        //     );
        // }

    // Still needs a review
    /** Facade: Get raw status row data by id */
        // public function getStatusRowById(int $id): ?array {
        //     return $this->statuses->getStatusRowById($id);
        // }

    /** Facade: Get StatusContext based on id ) */
        // public function getStatusById(int $statusId): ?StatusContext {
        //     return $this->statuses->getStatusById($statusId);
        // }

    /** Facade: Get all active statuses based on book id(s) */
        // public function getActiveStatus(array $bookIds): ?array {
        //     return $this->statuses->getActiveStatus($bookIds);
        // }

    /** API: Update the provided data for the edited status type */
        // TODO: Review why this isnt id based, but rather seems to be type/string based ?
        // public function updatePeriod(array $data): bool {
        //     try {
        //         $this->statuses->updatePeriod(
        //             $data['status_type'],
        //             $data['period_length'],
        //             $data['reminder_day'],
        //             $data['overdue_day']
        //         );
        //         return true;
        //     } catch (\Throwable $t) {
        //         error_log("[StatusService] " . $t->getMessage());
        //         return false;
        //     }
        // }

// TODO: Should now be obsolete, remove after the re-factor.
    /** API: Helper function to filter only active book statuses */
        // private function filterActive(array $all): array {
        //     return array_values(array_filter(
        //         $all,
        //         fn(StatusContext $s) => ($s->facade ?? 0) == 1
        //     ));
        // }

    /** API: Helper function to filter editable or selectable book statuses */
        // private function filterByFilter(array $all, string $filter): array {
        //     return array_values(array_filter(
        //         $all,
        //         fn(StatusContext $s) => ($s->filter ?? null) === $filter
        //     ));
        // }

    /** API: Request formatted status data for the change status function */
        // public function getAllFormatted(): array {
        //     $statusCTX = $this->getAll();

        //     return $statusCTX;
        //     $facade = $this->statusConfig['categories']['facade'];
        //     $exclude = $this->statusConfig['ui']['ui_select_filter'] ?? [];

        //     return array_values(array_filter($facade, fn($type) =>
        //         !in_array($type, $exclude, true)
        //     ));
        // }

    /** API: Get status types for the edit status action */
        // public function getEditableStatuses(): array {
        //     return array_values(array_filter(
        //         $this->getAll(),
        //         fn($s) => ($s['filter'] ?? null) === 'edit'
        //     ));
        // }