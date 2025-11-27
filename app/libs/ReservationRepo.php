<?php
namespace App;

use App\App;

class ReservationRepo {
    protected App\Database $db;
    
    public function __construct(\App\Database $db) {
        $this->db = $db;
    }
}