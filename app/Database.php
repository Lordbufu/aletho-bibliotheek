<?php

namespace App;

use App\App;
use App\Database\{Connection, Query, Installer};
use Throwable;

/**
 * Database service container.
 *
 * Acts as a single entry point for database-related services:
 * - Connection: low-level PDO connection
 * - Query: query execution helper
 * - Installer: schema/data installation manager
 */
class Database {
    protected Connection $connection; // Active DB connection
    protected Query $query;           // Query runner
    protected Installer $installer;   // Installer for schema/data

    /**
     * @param array $config Database configuration array
     */
    public function __construct(array $config) {
        try {
            // Create connection
            $this->connection = new Connection($config);

            // Create query helper
            $this->query = new Query($this->connection);

            // Create installer, optionally using schema_path from config
            $this->installer = new Installer(
                $this->connection,
                $config['schema_path'] ?? null
            );

        } catch (Throwable $e) {
            // Log and rethrow to prevent silent failures
            App::getServiceSafeLogger()->error(
                "Failed to initialize Database service: {$e->getMessage()}",
                'database'
            );
            throw $e;
        }
    }

    /**
     * Get the Connection instance.
     */
    public function connection(): Connection {
        return $this->connection;
    }

    /**
     * Get the Query instance.
     */
    public function query(): Query {
        return $this->query;
    }

    /**
     * Get the Installer instance.
     */
    public function installer(): Installer {
        return $this->installer;
    }
}