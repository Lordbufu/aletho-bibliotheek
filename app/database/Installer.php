<?php

namespace App\Database;

use DirectoryIterator;
use Throwable;
use App\App;

class Installer {
    protected Connection $connection;
    protected Query $query;
    protected string $schemaPath;
    protected string $dataPath;
    protected string $lockFile;

    public function __construct(Connection $connection, ?string $schemaPath = null) {
        $this->connection = $connection;
        $this->query      = new \App\Database\Query($this->connection);

        // Resolve paths
        $this->schemaPath = realpath($schemaPath ?? dirname(__DIR__, 2) . '/ext/schema') ?: '';
        $this->dataPath   = $this->schemaPath . '/data';
        $this->lockFile   = dirname(__DIR__, 2) . '/.installed.lock';

        // Defensive check
        if (!$this->schemaPath || !is_dir($this->schemaPath)) {
            App::getService('logger')->error(
                "Schema directory not found at: {$this->schemaPath}",
                'installer'
            );
        }
    }

    /**
     * Determine required table names by scanning schema directory.
     * Matches files like "001_users.sql" → "users".
     */
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
        } catch (Throwable $e) {
            App::getService('logger')->error(
                "Error reading schema directory: {$e->getMessage()}",
                'installer'
            );
        }

        return array_unique($tables);
    }

    /**
     * Execute all SQL scripts in a given path.
     * Returns an array of metadata about executed files.
     */
    protected function runScripts(string $path): array {
        $executed = [];

        if (!is_dir($path)) {
            App::getService('logger')->warning("Script path does not exist: {$path}", 'installer');
            return $executed;
        }

        $files = [];
        try {
            foreach (new DirectoryIterator($path) as $file) {
                if ($file->isFile() && preg_match('/^\d+_.*\.sql$/i', $file->getFilename())) {
                    $files[] = $file->getPathname();
                }
            }
        } catch (Throwable $e) {
            App::getService('logger')->error("Failed to list SQL files: {$e->getMessage()}", 'installer');
            return $executed;
        }

        sort($files, SORT_NATURAL | SORT_FLAG_CASE);

        foreach ($files as $filePath) {
            try {
                $sql = file_get_contents($filePath);
                if ($sql === false) {
                    App::getService('logger')->warning("Could not read file: {$filePath}", 'installer');
                    continue;
                }

                $this->query->run($sql);

                $executed[] = [
                    'file' => basename($filePath),
                    'path' => $filePath,
                    'size' => filesize($filePath),
                ];
            } catch (Throwable $e) {
                App::getService('logger')->error(
                    "Failed executing SQL file {$filePath}: {$e->getMessage()}",
                    'installer'
                );
            }
        }

        return $executed;
    }

    /**
     * Install schema and (optionally) seed data.
     */
    public function install(bool $withData = false): void {
        try {
            $manifest = [];
            // If installed but also not withData tag was included, log and skip.
            if ($this->isInstalled() && !$withData) {
                App::getService('logger')->warning("Install skipped: already installed", 'installer');
                return;
            } 

            // If not installed, install tables regardless of the withData tag.
            if (!$this->isInstalled()) {
                App::getService('logger')->warning("Starting installation...", 'installer');
                $executedSchema = $this->runScripts($this->schemaPath);
                $manifest['installed_at'] = date('c');
                $manifest['schema_files'] = $executedSchema;
            }

            if($withData) {
                $executedData = $this->runScripts($this->dataPath);
                $manifest['data_files'] = $executedData;
            }

            if (file_put_contents(
                $this->lockFile,
                json_encode($manifest, JSON_PRETTY_PRINT)
            ) === false) {
                App::getService('logger')->error("Failed to write lock file: {$this->lockFile}", 'installer');
            } else {
                App::getService('logger')->warning("Installation completed successfully", 'installer');
            }

        } catch (Throwable $e) {
            App::getService('logger')->error(
                "Installation failed: {$e->getMessage()}",
                'installer'
            );
        }
    }

    /**
     * Check if the system is already installed by:
     * 1. Presence of lock file.
     * 2. All required tables existing in the DB.
     */
    public function isInstalled(): bool {
        if (!file_exists($this->lockFile)) {
            App::getService('logger')->warning("Lock file not found", 'installer');
            return false;
        }

        try {
            $existing = $this->query->run('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);

            foreach ($this->requiredTables() as $required) {
                if (!in_array($required, $existing, false)) {
                    App::getService('logger')->warning(
                        "Required table missing: {$required}",
                        'installer'
                    );
                    return false;
                }
            }
        } catch (Throwable $e) {
            App::getService('logger')->error(
                "isInstalled check failed: {$e->getMessage()}",
                'installer'
            );
            return false;
        }

        return true;
    }
}