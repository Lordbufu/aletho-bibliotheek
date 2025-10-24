<?php
namespace App;

use App\Exceptions\BootException;

/*  Core application bootstrapper and service accessor. */
class App {
    protected static Services $container;

    /*  Boot the application by loading and initializing all services. */
    public static function boot(): bool {
        try {
            $configPath = BASE_PATH . '/ext/config/services.php';
            $serviceDefinitions = require $configPath;

            foreach ($serviceDefinitions as $name => &$definition) {
                if ($name === 'database' && isset($definition['config']) && is_file($definition['config'])) {
                    $dbConfig = require $definition['config'];
                    $dbConfig['schema_path'] = BASE_PATH . '/ext/schema';

                    $class = $definition['class'];
                    $definition = fn() => new $class($dbConfig);
                }
            }
            unset($definition);

            static::$container = new Services($serviceDefinitions);

            $criticalServices = ['database', 'router'];

            foreach ($criticalServices as $name) {
                try {
                    $instance = static::$container->get($name);

                    if ($name === 'router') {
                        $routerConfig = $serviceDefinitions[$name]['config'] ?? null;
                        if ($routerConfig && is_file($routerConfig)) {
                            $router = $instance;
                            require $routerConfig;
                        }
                    }
                    if ($name === 'database') {
                        if (!$instance->installer()->isInstalled()) {
                            $instance->installer()->install(true);
                        }
                    }
                } catch (\Throwable $t) {
                    throw new \RuntimeException("Service '{$name}' failed", 0, $t);
                }
            }

            return true;
        } catch (\Throwable $t) {
            throw new \RuntimeException("Service '{$name}' failed", 0, $t);
            return false;
        }
    }

    /*  Retrieve a service from the container. */
    public static function getService(string $name): mixed {
        return self::$container->get($name);
    }

    /*  Render a view file with optional data. */
    public static function view(string $template, array $data = []): void {
        $baseDir = __DIR__ . '/../ext/views/';
        $file = $baseDir . $template . '.view.php';

        if (!file_exists($file)) {
            throw new \Exception("View '{$template}' not found at {$file}");
        }

        if (!empty($data)) {
            extract($data, EXTR_SKIP);
        }

        require $file;
    }

    /*  Redirect to a given URL. */
    public static function redirect(string $url): void {
        header("Location: {$url}");
        exit;
    }
}