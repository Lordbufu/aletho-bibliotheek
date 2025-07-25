<?php
namespace App\Core\Database;

use PDO, RuntimeException, DateTime;
use App\Core\App;

class Installer {
    protected PDO    $pdo;
    protected string $schemaDir;
    protected string $lockFile;
    protected string $dbName;

    public function __construct(PDO $pdo, string $schemaDir, string $lockFile) {
        $this->pdo       = $pdo;
        $this->schemaDir = rtrim($schemaDir, '/');
        $this->lockFile  = $lockFile;
        $this->dbName    = $pdo->query('SELECT DATABASE()')->fetchColumn();
    }

    /**
     * Return an array of missing tables (file + table name) or false if everything's installed.
     */
    public function getMissingTables(): array|false {
        if($this->isInstalled()) {                                         // If already installed, bail
            return false;
        }

        $missing = [];
        $files = glob($this->schemaDir.'/*.sql');                           // Sort files by filename
        sort($files);                                                       // Lexicographically by filename

        foreach($files as $path) {
            $table = preg_replace('/^\d+_/', '', basename($path, '.sql'));  // Strip numeric prefix

            $stmt = $this->pdo->prepare(
                "SELECT 1 
                   FROM information_schema.tables 
                  WHERE table_schema = :schema 
                    AND table_name   = :table 
                  LIMIT 1"
            );

            $stmt->execute([
                ':schema' => $this->dbName,
                ':table'  => $table,
            ]);

            if(!$stmt->fetchColumn()) {
                $missing[] = ['file' => $path, 'table' => $table];
            }
        }

        if(empty($missing)) {                                               // if nothing missing, mark installed
            $this->markInstalled([]);
            return false;
        }

        return $missing;
    }

    /**
     * Execute SQL for each missing table; on success, mark the lock as installed.
     * Throws RuntimeException on any failure.
     */
    public function installTables(array $tables): void {
        $installed = [];

        foreach($tables as $entry) {
            $file  = $entry['file'];
            $table = $entry['table'];

            if(!is_readable($file)) {
                App::get('logger')->error("SQL file missing for table '{$table}' at '{$file}'", 'installer');
                throw new RuntimeException("SQL file for table '{$table}' not readable at '{$file}'.");
            }

            $sql = file_get_contents($file);

            try {
                $this->pdo->exec($sql);
                $installed[] = $table;
                App::get('logger')->warning("Successfully created table '{$table}'.", 'installer');
            } catch(\Throwable $e) {
                if (str_contains($e->getMessage(), 'already exists')) {
                    // Log and skip, don't crash the install
                    App::get('logger')->warning("Skipped table '{$table}': already exists.", 'installer');
                } else {
                    App::get('logger')->error("Failed to install table '{$table}': " . $e->getMessage(), 'installer');
                    throw new \RuntimeException("Error installing table '{$table}': " . $e->getMessage(), 0, $e);
                }
            }
        }

        $this->markInstalled($installed);
        App::get('logger')->warning("Installer completed. Tables installed: [" . implode(', ', $installed) . "]", 'installer');
    }

    /**
     * Check lock-file JSON for { status: "installed", … }.
     */
    protected function isInstalled(): bool {
        if(!is_file($this->lockFile)) {
            return false;
        }

        $data = json_decode(file_get_contents($this->lockFile), true);
        return isset($data['status']) && $data['status'] === 'installed';
    }

    /**
     * Write a JSON lock with timestamp and table list.
     */
    protected function markInstalled(array $tables): void {
        $data = [
            'status'    => 'installed',
            'timestamp' => (new DateTime())->format(DateTime::ATOM),
            'tables'    => $tables,
        ];

        file_put_contents($this->lockFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Return install details or null if never run.
     */
    public function getInstallDetails(): ?array {
        if(!is_file($this->lockFile)) {
            return null;
        }

        return json_decode(file_get_contents($this->lockFile), true);
    }
}