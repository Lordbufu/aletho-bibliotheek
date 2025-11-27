<?php
namespace App;

/*  Core application bootstrapper and service accessor. */
class App {
    protected static Services   $container;
    protected static Libraries  $libraries;

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
                            $routes = require $routerConfig;
                            $instance->loadRoutes($routes);
                        }
                    }
                    
                    if ($name === 'database') {
                        $installer = $instance->installer();

                        if (!$installer->isInstalled()) {
                            $installer->install(true);
                        }
                    }
                } catch (\Throwable $t) {
                    throw new \RuntimeException("Service '{$name}' failed", 0, $t);
                }
            }

            self::$libraries = new Libraries(self::$container->get('database'));

            return true;
        } catch (\Throwable $t) {
            throw new \RuntimeException("Service '{$name}' failed", 0, $t);
            return false;
        }
    }

    /** Retrieve a service from the container */
    public static function getService(string $name): mixed {
        return self::$container->get($name);
    }

    /** Retrieve a libraries from the container */
    public static function getLibraries() {
        if (self::$libraries === null) {
            throw new \RuntimeException('Libraries not booted');
        }

        return self::$libraries;
    }

    /** Render a view file with optional data */
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

    /** Redirect to a given URL */
    public static function redirect(string $url): void {
        header("Location: {$url}");
        exit;
    }
}