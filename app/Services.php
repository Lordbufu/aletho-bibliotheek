<?php

namespace App;

use App\App;
use Throwable;

/**
 * Simple service container / dependency injector.
 *
 * Loads service definitions from a config file or array and instantiates them on demand.
 */
class Services {
    /** @var array<string, mixed> */
    protected array $instances = [];

    /** @var array<string, mixed> */
    protected array $factories = [];

    /** Construct the Services container.
     *      @param string|array $configFile Path to a PHP config file returning an array, or an array of service definitions.
     *      @throws \RuntimeException|\InvalidArgumentException
     */
    public function __construct(string|array $configFile) {
        if (is_string($configFile)) {
            if (!is_file($configFile)) {
                throw new \RuntimeException("Service config not found: {$configFile}");
            }

            $factories = require $configFile;

            if (!is_array($factories)) {
                throw new \RuntimeException("Service config must return an array.");
            }

            $this->factories = $factories;
        } elseif (is_array($configFile)) {
            $this->factories = $configFile;
        } else {
            throw new \InvalidArgumentException('Config must be a file path or an array.');
        }

        App::getServiceSafeLogger()->warning(
            "Services container initialized with " . count($this->factories) . " definitions",
            'services'
        );
    }

    /** Retrieve a service instance by name, instantiating it if necessary.
     *      @param string $name Service name
     *      @return mixed The service instance
     *      @throws \InvalidArgumentException|\RuntimeException
     */
    public function get(string $name) {
        App::getServiceSafeLogger()->warning("Resolving service: {$name}");

        // Return already-instantiated service
        if (isset($this->instances[$name])) {
            App::getServiceSafeLogger()->warning(
                "Service '{$name}' retrieved from cache",
                'services'
            );
            return $this->instances[$name];
        }

        // Is this a known factory/service definition?
        if (!isset($this->factories[$name])) {
            throw new \InvalidArgumentException("Service '{$name}' not registered.");
        }

        $definition = $this->factories[$name];

        try {
            if (is_callable($definition)) {
                // Closures or [ClassName, 'method'] callbacks
                $this->instances[$name] = $definition();
            } elseif (is_array($definition) && isset($definition['class'])) {
                // Declarative array: build it
                $class = $definition['class'];
                $instance = new $class();

                // Optional config file include
                if (!empty($definition['config']) && is_file($definition['config'])) {
                    if ($name === 'router') {
                        $router = $instance; // Make available to config file
                    }
                    require $definition['config'];
                }

                $this->instances[$name] = $instance;
            } else {
                throw new \RuntimeException("Invalid service definition for '{$name}'");
            }

            App::getService('logger')->warning(
                "Service '{$name}' instantiated",
                'services'
            );

            return $this->instances[$name];
        } catch (Throwable $e) {
            App::getService('logger')->error(
                "Error creating service '{$name}': {$e->getMessage()}",
                'services'
            );
            throw $e;
        }
    }
}
