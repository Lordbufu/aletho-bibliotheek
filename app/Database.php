<?php

namespace App;

use App\Database\{Connection, Query, Installer};

class Database {
    protected Connection $connection;
    protected Query $query;
    protected Installer $installer;

    public function __construct(array $config) {
        $this->connection = new Connection($config);
        $this->query      = new Query($this->connection);
        $this->installer  = new Installer($this->connection, $config['schema_path'] ?? null);
    }

    public function connection(): Connection {
        return $this->connection;
    }

    public function query(): Query {
        return $this->query;
    }

    public function installer(): Installer {
        return $this->installer;
    }
}