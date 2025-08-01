<?php
// TODO: Review why the rootPath is not working, and the logs are stored in the wrong place.
namespace App\Core\Services;

class LoggerService {
    protected string $logDir;

    /**
     * Initialize the LoggerService.
     * Creates the log directory if it doesn't exist.
     */
    public function __construct() {
        $this->logDir = BASE_PATH . '/storage/logs';        // Use the BASE_PATH define in the index.php

        if(!is_dir($this->logDir)) {
            mkdir($this->logDir, 0775, true);
        }
    }

    /**
     * Write a log entry to the appropriate file.
     *
     * @param string $type The type of log (e.g., 'error', 'warning', 'info').
     * @param string $message The message to log.
     * @param string $context The context of the log (e.g., 'app', 'db').
     */
    protected function writeLog(string $type, string $message, string $context): void {
        $file = "{$this->logDir}/{$context}_{$type}.log";
        $entry = sprintf("[%s] %s\n", date('c'), $message);
        file_put_contents($file, $entry, FILE_APPEND);
    }

    /**
     * Log a warning message.
     *
     * @param string $message The message to log.
     * @param string $context The context of the log (e.g., 'app', 'db').
     */
    public function warning(string $message, string $context = 'app'): void {
        $this->writeLog('warning', $message, $context);
    }

    /**
     * Log a error message.
     *
     * @param string $message The message to log.
     * @param string $context The context of the log (e.g., 'app', 'db').
     */
    public function error(string $message, string $context = 'app'): void {
        $this->writeLog('error', $message, $context);
    }
}