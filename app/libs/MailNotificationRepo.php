<?php
namespace App\Libs;

final class MailNotificationRepo {
    private \App\Database $db;
    
    public function __construct() {
        $this->db = \App\App::getService('database');
    }

    /** */
    // Currently un-used
    public function getStatusNotiRow(int $bookStatusId, int $notiId): array {
        $row = $this->db->query()->fetchOne("
                SELECT *
                FROM status_noti
                WHERE bk_st_id = :sId
                AND notification_id = :nId
            ", [
                'sId' => $bookStatusId,
                'nId' => $notiId
            ]);
        return $row;
    }

    /** API: Get `notifications` data based on the `type` string   */
    public function getNotificationByType($notiType) {
        $row = $this->db->query()->fetchOne("
            SELECT id, template_id
            FROM notifications
            WHERE type = :type
        ", ['type' => $notiType]);

        return $row ?: null;
    }

    /** API: Get template by notification id */
    public function getTemplateByNotiId(int $templId): ?array {
        $sql = "SELECT
                    subject, body_html, from_mail, from_name, body_text 
                FROM
                    mail_templates
                WHERE
                    id = :templId
                AND
                    active = 1
                LIMIT 1
            ";

        return $this->db->query()->fetchOne($sql, [
            'templId' => $templId
        ]);
    }

    /** API: Store the request status notification links in the `status_noti` link table */
    public function linkStatusNotification(int $bookStatusId, int $statusId, int $notificationId): void {
        $sql = "
            INSERT INTO status_noti
                (bk_st_id, status_id, notification_id, mail_send, sent_at)
            VALUES
                (:bk, :status, :noti, 0, NULL)
        ";

        $this->db->query()->run($sql, [
            'bk'     => $bookStatusId,
            'status' => $statusId,
            'noti'   => $notificationId
        ]);
    }

    /** API: Update `status_noti` that the mail was send, using `book_status`.`id` and `notification`.`id` */
    public function markNotificationSent(int $bsId, int $notiId): void {
        $sql = "
            UPDATE
                status_noti
            SET
                mail_send   = 1,
                sent_at     = current_timestamp()
            WHERE
                bk_st_id = :bsId
            AND
                notification_id = :notiId
        ";

        $this->db->query()->run($sql, [
            'bsId'     => $bsId,
            'notiId' => $notiId
        ]);
    }
}