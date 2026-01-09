<?php
namespace App\Database;

use App\App;
use PDO;
use PDOException;

class Connection {
    protected PDO $pdo;

    /** API: Construct the Connection class using provided configuration */
    public function __construct(array $config) {
        $requiredKeys = ['driver', 'host', 'port', 'database', 'charset', 'username', 'password'];
        foreach ($requiredKeys as $key) {
            if (!isset($config[$key])) {
                throw new \InvalidArgumentException("Missing database config key: {$key}");
            }
        }

        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            $config['driver'],
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        try {
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            throw new \RuntimeException("Database connection failed. Please contact support.");
        }
    }

    /** API: Get the underlying PDO instance */
    public function pdo(): PDO {
        return $this->pdo;
    }

    /** API: Start a new database transaction */
    public function startTransaction(): bool {
        try {
            if ($this->isTransactionActive()) {
                return true;
            }

            $started = $this->pdo->beginTransaction();

            return $started;
        } catch (\Throwable $t) {
            throw $t;
            return false;
        }
    }

    /** API: Commit the current database transaction */
    public function finishTransaction(): bool {
        try {
            return $this->pdo->commit();
        } catch (\PDOException $e) {
            throw $e;
            return false;
        }
    }

    /** API: Rollback the current database transaction */
    public function cancelTransaction(): bool {
        try {
            return $this->pdo->rollBack();
        } catch (\PDOException $e) {
            throw $e;
            return false;
        }
    }

    /** API: Check if a transaction is currently active */
    public function isTransactionActive(): bool {
        return $this->pdo->inTransaction();
    }
}