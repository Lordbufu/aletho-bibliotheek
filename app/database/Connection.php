<?php
namespace App\Database;

use App\App;
use PDO;
use PDOException;

class Connection {
    protected PDO $pdo;

    /** Constructor initializes the PDO connection using provided configuration.
     *  Logs warnings for missing config values and errors for connection failures.
     *      @param array $config Database configuration with keys:
     *          'driver', 'host', 'port', 'database', 'charset', 'username', 'password'
     *      @throws \RuntimeException if connection fails
     *      @throws \InvalidArgumentException if required config keys are missing
     */
    public function __construct(array $config) {
        $safeLogger = App::getServiceSafeLogger();

        $requiredKeys = ['driver', 'host', 'port', 'database', 'charset', 'username', 'password'];
        foreach ($requiredKeys as $key) {
            if (!isset($config[$key])) {
                $safeLogger->warning(
                    "Missing database config key: {$key}",
                    'app'
                );
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
            $safeLogger->error(
                "Database connection failed: " . $e->getMessage(),
                'app'
            );

            throw new \RuntimeException("Database connection failed: " . $e->getMessage());
        }
    }

    /** Get the active PDO instance.
     *    @return PDO The active PDO connection instance
     */
    public function pdo(): PDO {
        return $this->pdo;
    }

    /** Start a new database transaction.
     *      @return bool True on success, false on failure.
     */
    public function startTransaction(): bool {
        try {
            if ($this->isTransactionActive()) {
                App::getService('logger')->error(
                    "PDO transaction already active",
                    'db'
                );
            }

            $started = $this->pdo->beginTransaction();

            if (!$started) {
                App::getService('logger')->error(
                    "Failed to start PDO transaction",
                    'db'
                );
            }

            return $started;
        } catch (\PDOException $e) {
            App::getServiceSafeLogger()->error(
                "Could not start transaction: " . $e->getMessage(),
                'db'
            );

            return false;
        }
    }

    /** Finalize the current transaction.
     *      @return bool True if committed, false otherwise.
     */
    public function finishTransaction(): bool {
        try {
            return $this->pdo->commit();
        } catch (\PDOException $e) {
            App::getServiceSafeLogger()->error(
                "Could not commit transaction: " . $e->getMessage(),
                'db'
            );

            return false;
        }
    }

    /** Cancel the current transaction.
     *      @return bool True if rolled back, false otherwise.
     */
    public function cancelTransaction(): bool {
        try {
            return $this->pdo->rollBack();
        } catch (\PDOException $e) {
            App::getServiceSafeLogger()->error(
                "Could not roll back transaction: " . $e->getMessage(),
                'db'
            );
            
            return false;
        }
    }

    /** Check if a transaction is currently active.
     *      @return bool
     */
    public function isTransactionActive(): bool {
        return $this->pdo->inTransaction();
    }
}