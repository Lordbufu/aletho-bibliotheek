<?php

namespace App\Database;

use DirectoryIterator;
use App\App;

/**
 * Handles initial database setup by executing schema and optional seed data scripts.
 * Uses a lock file to prevent reinstallation and verifies required tables exist.
 */
class Installer {
    protected Connection $connection;
    protected Query $query;
    protected string $schemaPath;
    protected string $dataPath;
    protected string $lockFile;

    public function __construct(Connection $connection, ?string $schemaPath = null) {
        $this->connection = $connection;
        $this->query      = new \App\Database\Query($this->connection);
        $this->schemaPath = realpath($schemaPath ?? dirname(__DIR__, 2) . '/ext/schema') ?: '';
        $this->dataPath   = $this->schemaPath . '/data';
        $this->lockFile   = dirname(__DIR__, 2) . '/.installed.lock';

        if (!$this->schemaPath || !is_dir($this->schemaPath)) {
            throw new \RuntimeException(
                "Schema directory not found at: {$this->schemaPath}"
            );
        }
    }

    /*  Scan schema directory for required table names, matches files like "001_users.sql" → "users". */
    protected function requiredTables(): array {
        $tables = [];

        try {
            foreach (new DirectoryIterator($this->schemaPath) as $file) {
                if (!$file->isFile()) {
                    continue;
                }

                if (preg_match('/^\d+_([^.]+)\.sql$/i', $file->getFilename(), $matches)) {
                    $tables[] = $matches[1];
                }
            }
        } catch (\Throwable $t) {
            throw $t;
        }

        return array_unique($tables);
    }

    /*  Execute all SQL scripts in a given directory. */
    protected function runScripts(string $path): array {
        $executed = [];

        if (!is_dir($path)) {
            return $executed;
        }

        $files = [];
        try {
            foreach (new DirectoryIterator($path) as $file) {
                if ($file->isFile() && preg_match('/^\d+_.*\.sql$/i', $file->getFilename())) {
                    $files[] = $file->getPathname();
                }
            }
        } catch (\Throwable $t) {
            throw $t;
            return $executed;
        }

        sort($files, SORT_NATURAL | SORT_FLAG_CASE);

        foreach ($files as $filePath) {
            try {
                $sql = file_get_contents($filePath);
                if ($sql === false) {
                    continue;
                }

                $this->query->run($sql);

                $executed[] = [
                    'file' => basename($filePath),
                    'path' => $filePath,
                    'size' => filesize($filePath),
                ];
            } catch (\Throwable $t) {
                throw $t;
            }
        }

        return $executed;
    }

    /*  Perform installation of schema and optional seed data. */
    public function install(bool $withData = false): void {
        try {
            $manifest = [];

            if ($this->isInstalled() && !$withData) {
                return;
            } 

            if (!$this->isInstalled()) {
                $executedSchema = $this->runScripts($this->schemaPath);
                $manifest['installed_at'] = date('c');
                $manifest['schema_files'] = $executedSchema;
            }

            if ($withData) {
                $executedData = $this->runScripts($this->dataPath);
                $manifest['data_files'] = $executedData;
            }

            try {
                file_put_contents($this->lockFile, json_encode($manifest, JSON_PRETTY_PRINT));
            } catch (\Throwable $t) {
                throw $t;
            }
        } catch (\Throwable $t) {
            throw $t;
        }
    }

    /*  Check if the system is already installed. */
    public function isInstalled(): bool {
        if (!file_exists($this->lockFile)) {
            return false;
        }

        try {
            $existing = $this->query->run('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);

            foreach ($this->requiredTables() as $required) {
                if (!in_array($required, $existing, false)) {
                    return false;
                }
            }
        } catch (Throwable $t) {
            throw $t;
            return false;
        }

        return true;
    }
}