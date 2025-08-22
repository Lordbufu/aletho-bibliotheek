<?php
namespace App\Database;

use App\App;
use PDO;
use PDOException;

class Connection {
    protected PDO $pdo;

    /**
     * Constructor initializes the PDO connection using provided configuration.
     * Logs warnings for missing config values and errors for connection failures.
     */
    public function __construct(array $config) {
        $safeLogger = App::getServiceSafeLogger();

        // Validate required config keys
        $requiredKeys = ['driver', 'host', 'port', 'database', 'charset', 'username', 'password'];
        foreach ($requiredKeys as $key) {
            if (!isset($config[$key])) {
                $safeLogger->warning("Missing database config key: {$key}", 'app');
            }
        }

        // Build DSN string for PDO
        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            $config['driver'],
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        try {
            // Attempt to create PDO instance with error and fetch mode settings
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            // Log error and rethrow as RuntimeException
            $safeLogger->error("Database connection failed: " . $e->getMessage(), 'app');
            throw new \RuntimeException("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Returns the active PDO instance.
     */
    public function pdo(): PDO {
        return $this->pdo;
    }
}