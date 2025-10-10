<?php

namespace App\Database;

use PDO;
use PDOStatement;
use App\App;

/* Lightweight query runner for executing SQL statements, with optional parameter binding and convenience fetch methods. */
class Query {
    protected Connection $connection;

    /** Construct the Query class, using the active connection.
     *      @param Connection $connection Active DB connection instance
     */
    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    /** Prepare and execute an SQL statement with optional parameters.
     *      @param string $sql    SQL query string
     *      @param array  $params Parameters to bind to the query
     *      @return bool|PDOStatement Returns PDOStatement on success, false on failure
     */
    public function run(string $sql, array $params = []): bool|PDOStatement {
        try {
            $stmt = $this->connection->pdo()->prepare($sql);
            $stmt->execute($params);

            return $stmt;
        } catch (\PDOException $e) {
            // Log the error instead of halting execution
            App::getService('logger')->error(
                "SQL execution failed: {$e->getMessage()} | SQL: {$sql} | Params: " . json_encode($params),
                'query'
            );

            return false;
        }
    }

    /** Fetch all rows from a query.
     *      @param string $sql
     *      @param array  $params
     *      @return array
     */
    public function fetchAll(string $sql, array $params = []): array {
        $stmt = $this->run($sql, $params);

        return $stmt ? $stmt->fetchAll() : [];
    }

    /** Fetch a single row from a query.
     *      @param string $sql
     *      @param array  $params
     *      @return array|null
     */
    public function fetchOne(string $sql, array $params = []): ?array {
        $stmt = $this->run($sql, $params);

        if (!$stmt) {
            return null;
        }

        $result = $stmt->fetch();

        return $result !== false ? $result : null;
    }

    /** Fetch a single scalar value from a query.
     *      @param string $sql
     *      @param array  $params
     *      @return mixed
     */
    public function value(string $sql, array $params = []): mixed {
        $stmt = $this->run($sql, $params);
        return $stmt ? $stmt->fetchColumn() : null;
    }

    /** Return index key from last query operration.
     *      @return string  -> index key from last query operration
     */
    public function lastInsertId(): string {
        return $this->connection->pdo()->lastInsertId();
    }
}