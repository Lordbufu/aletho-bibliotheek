<?php

namespace App\Database;

use PDO;
use PDOStatement;
use App\App;

/** Lightweight query runner for executing SQL statements, with optional parameter binding and convenience fetch methods */
class Query {
    protected Connection $connection;

    /** Construct the Query class, using the active connection */
    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    /** Prepare and execute an SQL statement with optional parameters */
    public function run(string $sql, array $params = []): bool|\PDOStatement {
        try {
            $stmt = $this->connection->pdo()->prepare($sql);
            $stmt->execute($params);

            return $stmt;
        } catch (\PDOException $e) {
            throw $e;
            return false;
        }
    }

    /** Fetch all rows from a query */
    public function fetchAll(string $sql, array $params = []): array {
        $stmt = $this->run($sql, $params);

        return $stmt ? $stmt->fetchAll() : [];
    }

    /** Fetch a single row from a query */
    public function fetchOne(string $sql, array $params = []): ?array {
        $stmt = $this->run($sql, $params);

        if (!$stmt) {
            return null;
        }

        $result = $stmt->fetch();

        return $result !== false ? $result : null;
    }

    /** Fetch a single scalar value from a query */
    public function value(string $sql, array $params = []): mixed {
        $stmt = $this->run($sql, $params);
        return $stmt ? $stmt->fetchColumn() : null;
    }

    /** Return index key from last query operration */
    public function lastInsertId(): string {
        return $this->connection->pdo()->lastInsertId();
    }
}