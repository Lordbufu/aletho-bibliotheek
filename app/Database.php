<?php

namespace App;

use App\App;
use App\Database\{Connection, Query, Installer};

/** Database service container:
 * 
 *  Acts as a single entry point for database-related services:
 *      - Connection: low-level PDO connection
 *      - Query: query execution helper
 *      - Installer: schema/data installation manager
 */
class Database {
    protected Connection $connection;
    protected Query $query;
    protected Installer $installer;

    /** Constructor initializes database services.
     *      @param array $config Database configuration arrays
     */
    public function __construct(array $config) {
        try {
            $this->connection = new Connection($config);
            $this->query = new Query($this->connection);
            $this->installer = new Installer(
                $this->connection,
                $config['schema_path'] ?? null
            );
        } catch (Throwable $e) {
            App::getServiceSafeLogger()->error(
                "Failed to initialize Database service: {$e->getMessage()}",
                'database'
            );

            throw $e;
        }
    }

    /** Get the Connection instance.
     *    @return Connection The Connection instance
     */
    public function connection(): Connection {
        return $this->connection;
    }

    /** Get the Query instance.
     *    @return Query The Query instance
     */
    public function query(): Query {
        return $this->query;
    }

    /** Get the Installer instance.
     *    @return Installer The Installer instance
     */
    public function installer(): Installer {
        return $this->installer;
    }

    /** Start a new database transaction.
     *      @return bool True on success, false on failure.
     */
    public function startTransaction(): bool {
        return $this->connection->startTransaction();
    }

    /** Finalize the current transaction.
     *      @return bool True if committed, false otherwise.
     */
    public function finishTransaction(): bool {
        return $this->connection->finishTransaction();
    }

    /** Cancel the current transaction.
     *      @return bool True if rolled back, false otherwise.
     */
    public function cancelTransaction(): bool {
        return $this->connection->cancelTransaction();
    }

    /** Check if a transaction is currently active.
     *      @return bool
     */
    public function isTransactionActive(): bool {
        return $this->connection->isTransactionActive();
    }
}