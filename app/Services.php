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
    protected array $instances = [];
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
    }

    /** Retrieve a service instance by name, instantiating it if necessary.
     *      @param string $name Service name
     *      @return mixed The service instance
     *      @throws \InvalidArgumentException|\RuntimeException
     */
    public function get(string $name) {
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        if (!isset($this->factories[$name])) {
            throw new \InvalidArgumentException("Service '{$name}' not registered.");
        }

        $definition = $this->factories[$name];

        try {
            if (is_callable($definition)) {
                $this->instances[$name] = $definition();
            } elseif (is_array($definition) && isset($definition['class'])) {
                $class = $definition['class'];
                $instance = new $class();

                if (!empty($definition['config']) && is_file($definition['config'])) {
                    if ($name === 'router') {
                        $router = $instance;
                    }
                    require $definition['config'];
                }

                $this->instances[$name] = $instance;
            } else {
                throw new \RuntimeException("Invalid service definition for '{$name}'");
            }

            return $this->instances[$name];
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
