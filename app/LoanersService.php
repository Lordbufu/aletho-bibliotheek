<?php
namespace App;

use App\App;
use App\Libs\{LoanersRepo, StatusRepo};

class LoanersService {
    protected StatusRepo            $status;
    protected LoanersRepo           $loaners;
    protected NotificationService   $notificationService;
    protected Database              $db;
       
    protected array $statusEventMap = [
        2 => ['user' => 'loan_confirm'],                                    // Afwezig
        5 => ['user' => 'reserv_confirm'],                                  // Gereserveerd
        3 => ['office' => 'transport_request'],                             // Transport
        4 => ['user' => 'pickup_ready_confirm'],                            // Ligt Klaar
        6 => ['user' => 'overdue_reminder', 'office' => 'overdue_notice'],  // Overdatum
        // Example: adjust IDs to your actual statusId values
    ];

    public function __construct() {
        try {
            $this->db                   = App::getService('database');
            $this->notificationService  = App::getService('notification');
            $this->status               = new StatusRepo($this->db);
            $this->loaners              = new LoanersRepo($this->db);
        } catch (\Throwable $t) {
            throw $t;
        }
    }

    public function create(string $name, string $email): array {
        return $this->loaners->create($name, $email);
    }

    public function findById(int $id): ?array {
        return $this->loaners->findById($id);
    }

    public function findByName(string $query): array {
        return $this->loaners->findByName($query);
    }

    public function findByEmail(string $email): ?array {
        return $this->loaners->findByEmail($email);
    }

    public function update(int $id, array $fields): bool {
        return $this->loaners->update($id, $fields);
    }

    public function deactivate(int $id): bool {
        return $this->loaners->deactivate($id);
    }

    public function allActive(): array {
        return $this->loaners->allActive();
    }

    public function getCurrentLoanerByBookId(int $bookId): ?array {
        return $this->loaners->getCurrentLoanerByBookId($bookId);
    }

    public function getPreviousLoanersByBookId(int $bookId): array {
        return $this->loaners->getPreviousLoanersByBookId($bookId);
    }

    public function changeBookStatus($bookId, $statusId, $loanerName, $loanerEmail): bool {
        try {
            $this->db->startTransaction();

            // Ensure loaner exists
            $loaner = $this->loaners->findByEmail($loanerEmail);
            if (!$loaner) {
                $loaner = $this->loaners->create($loanerName, $loanerEmail);
            }
            $loanerId = $loaner['id'];

            // Persist status change
            $result = $this->status->setBookStatus(
                $bookId,
                $statusId,
                null,
                $loanerId,
                null,
                false
            );

            if (!$result) {
                throw new \RuntimeException('Status kon niet worden bijgewerkt');
            }

            $this->db->finishTransaction();

            // Build context for notifications
            $context = [
                ':book_id'    => $bookId,
                ':loaner_id'  => $loanerId,
                ':loaner_name'=> $loanerName,
                ':loaner_email'=> $loanerEmail,
                ':status_id'  => $statusId,
            ];

            // Send notifications based on mapping
            if (isset($this->statusEventMap[$statusId])) {
                foreach ($this->statusEventMap[$statusId] as $target => $event) {
                    try {
                        if ($target === 'user') {
                            $this->notificationService->notifyUser($loanerId, $event, $context);
                        } elseif ($target === 'office') {
                            // You’d need officeId in context; fetch from book or loaner
                            if (!empty($context[':office_id'])) {
                                $this->notificationService->notifyOffice($context[':office_id'], $event, $context);
                            }
                        }
                    } catch (\Throwable $t) {
                        error_log("[LoanerService] Notification failed: " . $t->getMessage());
                        // Don’t rethrow — status change succeeded
                    }
                }
            }

            return true;
        } catch (\Throwable $t) {
            $this->db->cancelTransaction();
            throw $t;
        }
    }
}