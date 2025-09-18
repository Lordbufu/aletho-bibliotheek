<?php

namespace App\Libs;

use App\App;

class StatusRepo {
    protected array $status;
    protected array $links;
    protected array $meta;

    /** (Done)
     * Helper function to calculate a status due date.
     * Formated for <input type="date">.
     */
    private function calculateDueDate(string $startDate, int $days): string {
        $dt = new \DateTimeImmutable($startDate);
        $interval = new \DateInterval("P{$days}D");

        return $dt
            ->add($interval)
            ->format('Y-m-d');
    }

    /** All default status data:
     * 
     *  - [id] = status id
     *  - [type] = status name
     *  - [periode_length] = periode length in days
     *  - [reminder_day] = amount of days before the period end, that a reminder should be sent
     *  - [overdue_day] = amount of days after the period end, that a book is considered overdue
     *  - [active] = if a status type is still active or not
     * 
     */
    public function getAllStatus(): array {
        if (!isset($this->status)) {
            $this->status = App::getService('database')
                ->query()
                ->fetchAll("SELECT * FROM status");
        }

        return $this->status;
    }

    /** All default book_status link table data:
     *  
     *  [book_id] = book id link
     *  [stat_id] = status id link
     *  [meta_id] = meta id link
     *  [loaner_id] = loaner id link
     *  [current_location] = current office id link
     *  [start_date] = date & time the status was started
     *  [send_mail] => If a mail was send for this status or not 
     * 
     */
    public function getAllLinks(): array {
        if (!isset($this->links)) {
            $this->links = App::getService('database')
                ->query()
                ->fetchAll("SELECT * FROM book_status");
        }

        return $this->links;
    }

    /** All default book_sta_meata link table data:
     * 
     *  [id] = default index
     *  [noti_id]       = notificatie id link
     *  [action_type]   = type/name of the action
     *  [action_token]  = unique token for this action
     *  [token_expires] = expire date & time for the token
     *  [token_used]    = was the token used yes/no
     *  [finished]      = was the action finished yes/no
     * 
     */
    public function getAllMeta(): array {
        if (!isset($this->meta)) {
            $this->meta = App::getService('database')
                ->query()
                ->fetchAll("SELECT * FROM book_sta_meta");
        }

        return $this->meta;
    }

    /** W.I.P.
     * 
     */
    public function getDisplayStatusByBookId(int $bookId): array {
        $statusMap = array_column($this->getAllStatus(), null, 'id');

        foreach ($this->getAllLinks() as $link) {
            if ((int)$link['book_id'] !== $bookId) {
                continue;
            }

            $status = $statusMap[$link['stat_id']] ?? null;

            if ($status && $status['active']) {
                return [
                    'status_id'         => $status['id'],
                    'status_name'       => $status['type'],
                    'status_exp'        => $this->calculateDueDate($link['start_date'], $status['periode_length'] ?? 0),
                    'current_loaner'    => $link['loaner_id'],
                    'location'          => $link['current_location']
                ];
            }
        }

        return[];
    }

    /** W.I.P.
     * 
     */
    public function getFullStatusInfoByBookId(int $bookId): array {
        $statusMap = array_column($this->getAllStatus(), null, 'id');
        $metaMap   = array_column($this->getAllMeta(), null, 'id');
        $result    = [];

        foreach ($this->getAllLinks() as $link) {
            if ((int)$link['book_id'] === $bookId) {
                $statusId = $link['stat_id'];
                $metaId   = $link['meta_id'];
                $status   = $statusMap[$statusId] ?? null;
                $meta     = $metaMap[$metaId] ?? null;

                $result[] = [
                    'status' => $status,
                    'meta'   => $meta,
                    'link'   => $link,
                ];
            }
        }

        return $result;
    }
}