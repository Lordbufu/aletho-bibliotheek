<?php

namespace App\Database;

use Throwable;
use RuntimeException;
use PDO;
use DirectoryIterator;
use App\App;

/**
 * Handles initial database setup by executing schema and optional seed data scripts.
 * Uses a lock file to prevent reinstallation and verifies required tables exist.
 */
class Installer {
    protected Connection    $connection;
    protected Query         $query;
    protected string        $schemaPath;
    protected string        $dataPath;
    protected string        $lockFile;

    /**
     * Holds detailed state of installation checks
     */
    protected array $installerState = [];

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

    /**
     * Collect lock file and directory context.
     */
    protected function getFileData(): array {
        $lock = [];
        if (file_exists($this->lockFile)) {
            $lock = json_decode(file_get_contents($this->lockFile), true) ?: [];
        }

        $schemaFiles = glob($this->schemaPath . '/*.sql') ?: [];
        $dataFiles   = glob($this->dataPath . '/*.sql') ?: [];

        return [
            'lock'   => $lock,
            'schema' => array_map('basename', $schemaFiles),
            'data'   => array_map('basename', $dataFiles),
        ];
    }

    /**
     * Check required tables against DB and lock file.
     */
    protected function checkTables(array $fileData): bool {
        try {
            $existingTables = $this->query
                ->run('SHOW TABLES')
                ->fetchAll(\PDO::FETCH_COLUMN);

            $lockedSchemas = $fileData['lock']['schema_files'] ?? [];
            $lockedFiles   = array_column($lockedSchemas, 'file');
            $schemaFiles   = $fileData['schema'] ?? [];

            $missingTables = [];
            foreach ($lockedSchemas as $schema) {
                $tableName = $this->extractTableName($schema['file']);
                if (!in_array($tableName, $existingTables, true)) {
                    $missingTables[] = $tableName;
                }
            }

            // NEW: detect schema files not yet in lock, but only if their table is missing
            $newFiles = [];
            foreach ($schemaFiles as $file) {
                if (!in_array($file, $lockedFiles, true)) {
                    $tableName = $this->extractTableName($file);
                    if (!in_array($tableName, $existingTables, true)) {
                        $newFiles[] = $file;
                    }
                }
            }

            $this->installerState['tables'] = [
                'existing' => $existingTables,
                'missing'  => $missingTables,
                'newFiles' => $newFiles,
            ];

            return empty($missingTables) && empty($newFiles);
        } catch (\Throwable $t) {
            $this->installerState['errors'][] = $t->getMessage();
            return false;
        }
    }

    /**
     * Check data files against lock file.
     * For now: only compares lock entries with directory presence.
     * Later: can query DB for actual seed rows.
     */
    protected function checkData(array $fileData): bool {
        $lockedData = $fileData['lock']['data_files'] ?? [];
        $lockedFiles = array_column($lockedData, 'file');
        $dataFiles   = $fileData['data'] ?? [];

        // Check for locked data files missing in filesystem
        $missing = [];
        foreach ($lockedData as $data) {
            if (!in_array($data['file'], $dataFiles, true)) {
                $missing[] = $data['file'];
            }
        }

        // NEW: detect data files present in filesystem but not yet in lock
        $newFiles = [];
        foreach ($dataFiles as $file) {
            if (!in_array($file, $lockedFiles, true)) {
                $newFiles[] = $file;
            }
        }

        $this->installerState['data'] = [
            'expected' => $lockedFiles,
            'present'  => $dataFiles,
            'missing'  => $missing,
            'newFiles' => $newFiles,
        ];

        return empty($missing) && empty($newFiles);
    }

    /**
     * Extract table name from schema filename.
     * Example: "01_users.sql" -> "users"
     */
    protected function extractTableName(string $filename): string {
        return preg_replace('/^\d+_/', '', pathinfo($filename, PATHINFO_FILENAME));
    }

    /**
     * Find schame file for table.
     */
    protected function findSchemaFileForTable(string $table): ?string {
        $files = glob($this->schemaPath . '/*.sql') ?: [];
        foreach ($files as $file) {
            if ($this->extractTableName(basename($file)) === $table) {
                return $file;
            }
        }
        return null;
    }

    /**
     * Install specific schema files (by filename).
     */
    protected function installTables(array $files): void {
        $manifest = file_exists($this->lockFile)
            ? json_decode(file_get_contents($this->lockFile), true) ?: []
            : [];

        foreach ($files as $file) {
            $path = $this->schemaPath . '/' . $file;
            if (is_file($path)) {
                $sql = file_get_contents($path);
                $this->query->run($sql);

                $manifest['schema_files'][] = [
                    'file' => basename($path),
                    'path' => $path,
                    'size' => filesize($path),
                ];
            }
        }

        $manifest['installed_at'] = date('c');
        file_put_contents($this->lockFile, json_encode($manifest, JSON_PRETTY_PRINT));
    }

    /**
     * Install specific data files (by filename).
     */
    protected function installData(array $files): void {
        $manifest = file_exists($this->lockFile)
            ? json_decode(file_get_contents($this->lockFile), true) ?: []
            : [];

        foreach ($files as $file) {
            $path = $this->dataPath . '/' . $file;
            if (is_file($path)) {
                $sql = file_get_contents($path);
                $this->query->run($sql);

                $manifest['data_files'][] = [
                    'file' => basename($path),
                    'path' => $path,
                    'size' => filesize($path),
                ];
            }
        }

        file_put_contents($this->lockFile, json_encode($manifest, JSON_PRETTY_PRINT));
    }

    /**
     * High-level check: is the system installed?
     */
    public function isInstalled(): bool {
        $fileData = $this->getFileData();

        $tablesOk = $this->checkTables($fileData);
        $dataOk   = $this->checkData($fileData);

        return $tablesOk && $dataOk;
    }

    /**
     * Expose detailed installer state for debugging or targeted installs.
     */
    public function getInstallerState(): array {
        return $this->installerState;
    }

    /**
     * Perform installation of schema and optional seed data.
     */  
    public function install(bool $withData = false): void {
        $state = $this->getInstallerState();

        if (!empty($state['tables']['missing']) || !empty($state['tables']['newFiles'])) {
            $this->installTables(array_merge(
                $state['tables']['missing'],
                $state['tables']['newFiles']
            ));
        }

        if ($withData) {
            $toInstall = array_merge(
                $state['data']['missing'] ?? [],
                $state['data']['newFiles'] ?? []
            );

            if (!empty($toInstall)) {
                $this->installData($toInstall);    
            }
        }
    }
}