<?php

namespace App;

class Logger {
    protected string $logDir;

    /**
     * Initialize the Logger.
     * Creates the log directory if it doesn't exist.
     */
    public function __construct() {
        $this->logDir = BASE_PATH . '/ext/storage/logs';

        if(!is_dir($this->logDir)) {
            mkdir($this->logDir, 0775, true);
        }

        $this->warning(
            "Service 'Logger' constructed",
            'services'
        );
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
     * Deletes log files in the log directory matching the given pattern.
     * Example: '/*_warning.log' will delete all warning logs.
     * You can pass other patterns to delete other tagged log files.
     * @param string $pattern Glob pattern for files to delete (default: '/*_warning.log')
     */
    protected function deleteTaggedLogs($pattern = '/*_warning.log') {
        if (!is_dir($this->logDir)) {
            return;
        }

        $files = glob($this->logDir . $pattern);

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Log a warning message unless in production environment.
     * In production, warning logs are deleted to keep log storage clean.
     *
     * @param string $message The message to log.
     * @param string $context The context of the log (e.g., 'app', 'db').
     */
    public function warning(string $message, string $context = 'app'): void {
        if ($_ENV['APP_ENV'] !== 'production') {
            $this->writeLog('warning', $message, $context);
        } else {
            $this->deleteTaggedLogs('/*_warning.log');
        }
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