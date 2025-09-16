<?php

namespace App;

use Throwable;
use App\Exceptions\BootException;

/**
 * Core application bootstrapper and service accessor.
 *
 * Handles:
 *  - Loading service definitions
 *  - Preprocessing special services before container creation
 *  - Instantiating all services and running post-init hooks
 *  - Providing global access to services and views
 */
class App {
    protected static Services $services;
    protected static array $bootErrors = [];

    /**
     * Boot the application by loading and initializing all services.
     *
     * @return bool True if boot completed without errors, false otherwise.
     */
    public static function boot(): bool {
        self::getServiceSafeLogger()->warning("Boot sequence started", 'app');

        try {
            $configPath = BASE_PATH . '/ext/config/services.php';
            $serviceDefinitions = require $configPath;

            // Pass 1: preprocess BEFORE creating container
            foreach ($serviceDefinitions as $name => &$definition) {
                if ($name === 'database' && isset($definition['config']) && is_file($definition['config'])) {
                    $dbConfig = require $definition['config'];
                    $dbConfig['schema_path'] = BASE_PATH . '/ext/schema';

                    $class = $definition['class'];
                    $definition = fn() => new $class($dbConfig);

                    self::getServiceSafeLogger()->warning("Database service preprocessed", 'app');
                }
            }
            unset($definition);

            // Pass 2: build container
            static::$services = new Services($serviceDefinitions);
            self::getServiceSafeLogger()->warning("Service container created", 'app');

            // Pass 3: eager‑load only critical services
            $criticalServices = ['database', 'router']; // adjust as needed

            foreach ($criticalServices as $name) {
                try {
                    $instance = static::$services->get($name);

                    if ($name === 'router') {
                        $routerConfig = $serviceDefinitions[$name]['config'] ?? null;
                        if ($routerConfig && is_file($routerConfig)) {
                            $router = $instance;
                            require $routerConfig;
                            self::getServiceSafeLogger()->warning("Router configured from {$routerConfig}", 'app');
                        }
                    }

                    if ($name === 'database') {
                        if (!$instance->installer()->isInstalled()) {
                            $instance->installer()->install(true);
                            self::getServiceSafeLogger()->warning("Database installed", 'app');
                        }
                    }
                } catch (\Throwable $e) {
                    static::$bootErrors[] = new \RuntimeException("Service '{$name}' failed: " . $e->getMessage(), 0, $e);
                    self::getServiceSafeLogger()->error("Service '{$name}' failed: {$e->getMessage()}", 'app');
                }
            }

            $success = empty(static::$bootErrors);

            self::getServiceSafeLogger()->warning(
                $success ? "Boot sequence completed successfully" : "Boot sequence completed with errors",
                'app'
            );

            return $success;

        } catch (\Throwable $t) {
            static::$bootErrors[] = $t;
            self::getServiceSafeLogger()->error("Fatal boot error: {$t->getMessage()}", 'app');
            return false;
        }
    }

    /**
     * Retrieve a service from the container.
     *
     * @param string $name
     * @return mixed
     */
    public static function getService(string $name) {
        return static::$services->get($name);
    }

    /**
     * Get boot error messages.
     *
     * @return string[]
     */
    public static function getBootErrors(): array {
        return array_map(fn($err) => $err->getMessage(), static::$bootErrors);
    }

    /**
     * Render a view file with optional data.
     *
     * @param string $name View name (without extension)
     * @param array  $data Variables to extract into the view scope
     * @throws \Exception If the view file is not found
     */
    public static function view(string $name, array $data = []): void {
        $baseDir = __DIR__ . '/../ext/views/';
        $file = $baseDir . $name . '.view.php';

        if (!file_exists($file)) {
            throw new \Exception("View '{$name}' not found at {$file}");
        }

        if (!empty($data)) {
            extract($data, EXTR_SKIP);
        }

        require $file;
    }

    /**
     * Redirect to url, to resume regular routing.
     * @param string $url the url we want to redirect to
     */
    public static function redirect(string $url): void {
        header("Location: {$url}");
        exit;
    }

    /**
     * Get a logger service if available, otherwise a null logger.
     */
    public static function getServiceSafeLogger() {
        try {
            // Only return the real logger if it's already been created
            if (isset(static::$services) && static::$services->hasInstance('logger')) {
                return static::$services->get('logger');
            }
        } catch (\Throwable) {
            // ignore and fall through to null logger
        }

        // Fallback null logger
        return new class {
            public function warning($msg) {}
            public function error($msg) {}
        };
    }

    public function hasInstance(string $name): bool {
        return isset($this->instances[$name]);
    }
}