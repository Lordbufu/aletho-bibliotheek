<?php

namespace App\Libs;

use App\Libs\Context\StatusTransitionContext;

final class StatusTransRepo {
    private \App\Database $db;

    public function __construct() {
        $this->db = \App\App::getService('database');
    }

    public function getTransitionByIds(int $from_status_id, int $to_status_id): ?StatusTransitionContext {
        $row = $this->db->query()->fetchOne(
            "SELECT * FROM status_transition WHERE from_status_id = :fromStId AND to_status_id = :toStId",
            [
                "fromStId" => $from_status_id,
                "toStId"   => $to_status_id
            ]
        );

        if (!$row) {
            return null;
        }
        
        return new StatusTransitionContext($row);
    }
}