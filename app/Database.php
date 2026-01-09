<?php

namespace App;

use App\App;
use App\Database\{Connection, Query, Installer};

/** Database service container */
class Database {
    protected Connection    $connection;
    protected Query         $query;
    protected Installer     $installer;

    /** Constructor initializes database services. */
    public function __construct(array $config) {
        try {
            $this->connection   = new Connection($config);
            $this->query        = new Query($this->connection);
            $this->installer    = new Installer(
                $this->connection,
                $config['schema_path'] ?? null
            );
        } catch (\Throwable $t) {
            throw $t;
        }
    }

    /** API: Get the Connection instance. */
    public function connection(): Connection {
        return $this->connection;
    }

    /** API: Get the Query instance. */
    public function query(): Query {
        return $this->query;
    }

    /** API: Get the Installer instance. */
    public function installer(): Installer {
        return $this->installer;
    }

    /** API: Transaction management methods */
    public function startTransaction(): bool {
        return $this->connection->startTransaction();
    }

    public function finishTransaction(): bool {
        return $this->connection->finishTransaction();
    }

    public function cancelTransaction(): bool {
        return $this->connection->cancelTransaction();
    }

    public function isTransactionActive(): bool {
        return $this->connection->isTransactionActive();
    }
}