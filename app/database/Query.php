<?php

namespace App\Database;

use PDO;

class Query {
    protected Connection $connection;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    public function run(string $sql, array $params = []): bool|\PDOStatement {
        try {
            $stmt = $this->connection->pdo()->prepare($sql);
            $stmt->execute($params);
        } catch (\PDOException $e) {
            die('SQL Error: ' . $e->getMessage());
        }
        
        return $stmt;
    }

    public function fetchAll(string $sql, array $params = []): array {
        return $this->run($sql, $params)->fetchAll();
    }

    public function fetchOne(string $sql, array $params = []): ?array {
        $result = $this->run($sql, $params)->fetch();
        return $result !== false ? $result : null;
    }

    public function value(string $sql, array $params = []) {
        return $this->run($sql, $params)->fetchColumn();
    }
}