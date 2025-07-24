<?php
namespace App\Core\Database;

use PDO;

class Database {
    protected PDO $pdo;

    public function __construct(array $cfg) {
        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s;charset=%s',
            $cfg['driver'],
            $cfg['host'],
            $cfg['port'],
            $cfg['database'],
            $cfg['charset']
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,    // echte prepares
        ];

        $this->pdo = new PDO($dsn, $cfg['username'], $cfg['password'], $options);
    }

    public function pdo(): PDO {
        return $this->pdo;
    }
}