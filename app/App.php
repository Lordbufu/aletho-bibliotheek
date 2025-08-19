<?php

namespace App;

class App {
    protected static Services $services;
    protected static array $bootErrors = [];

    public static function boot(): bool {
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
                }
            }

            unset($definition); // break before the second pass

            // Pass 2: build container
            static::$services = new Services($serviceDefinitions);

            // Pass 3: instantiate + do any after‑init work
            foreach (array_keys($serviceDefinitions) as $name) {
                try {
                    $instance = static::$services->get($name);

                    if ($name === 'router') {
                        $routerConfig = $serviceDefinitions[$name]['config'] ?? null;

                        if ($routerConfig && is_file($routerConfig)) {
                            $router = $instance;
                            require $routerConfig;
                        }
                    }

                    // If we are hooking in the database, check if default tables are set, and install everything if not.
                    if ($name === 'database') {
                        if ( !$instance->installer()->isInstalled() ) {
                            $instance->installer()->install(TRUE);
                        }
                    }
                } catch (\Throwable $e) {
                    static::$bootErrors[] = new \App\Exceptions\BootException("Service '{$name}' failed: " . $e->getMessage(), 0, $e);
                }
            }

            return empty(static::$bootErrors);
        } catch (\Throwable $t) {
            static::$bootErrors[] = $t;
            return false;
        }
    }

    public static function getService(string $name) {
        return static::$services->get($name);
    }

    public static function getBootErrors(): array {
        return array_map(fn($err) => $err->getMessage(), static::$bootErrors);
    }

    public static function view(string $name, array $data = []): void {
        $baseDir = __DIR__ . '/../ext/views/';
        $file = $baseDir . $name . '.view.php';

        if (!file_exists($file)) {
            throw new \Exception("View '{$name}' not found at {$file}");
        }

        if (!empty($data)) {
            extract($data, EXTR_SKIP); // Prevent overwriting existing vars
        }

        require $file;
    }
}