<?php
namespace App\Core\Database;

use PDO;

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
     * Scan schema/*.sql and return table-names that don't exist yet.
     * If none missing, write "installed" and return false.
     */
    public function getMissingTables() {
        // if already installed, bail
        if(is_file($this->lockFile) && trim(file_get_contents($this->lockFile)) === 'installed') {
            return false;
        }

        $missing = [];

        foreach(glob($this->schemaDir.'/*.sql') as $path) {
            $table = basename($path, '.sql');

            $stmt = $this->pdo->prepare("
              SELECT 1
                FROM information_schema.tables
               WHERE table_schema = :schema
                 AND table_name   = :table
               LIMIT 1
            ");

            $stmt->execute([':schema' => $this->dbName, ':table'  => $table ]);

            if(!$stmt->fetchColumn()) {
                $missing[] = $table;
            }
        }

        // if nothing missing, mark installed
        if(empty($missing)) {
            file_put_contents($this->lockFile, 'installed');
            return false;
        }

        return $missing;
    }

    /**
     * Execute each missing table's SQL file.
     * On *complete* success, write "installed" to the lock.
     */
    public function installTables(array $tables) {
        foreach($tables as $table) {
            $file = "{$this->schemaDir}/{$table}.sql";

            if(!is_readable($file)) {
                throw new \RuntimeException("SQL file not found: $file");
            }

            $sql = file_get_contents($file);

            try {
                $this->pdo->exec($sql);
                echo "✔ Created table $table\n";
            } catch(\Throwable $e) {
                throw new \RuntimeException("Failed creating table $table: ".$e->getMessage(), 0, $e);
            }
        }

        // only now mark everything done
        file_put_contents($this->lockFile, 'installed');
    }
}