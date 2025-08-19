<?php

namespace App;

class Services {
    protected array $instances = [];
    protected array $factories = [];

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

    public function get(string $name) {
        // Return already-instantiated service
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        // Is this a known factory/service definition?
        if (isset($this->factories[$name])) {
            $definition = $this->factories[$name];

            if (is_callable($definition)) {
                // 1️⃣ Closures or [ClassName, 'method'] callbacks
                $this->instances[$name] = $definition();
            } elseif (is_array($definition) && isset($definition['class'])) {
                // 2️⃣ Declarative array: build it
                $class = $definition['class'];
                $instance = new $class();

                // Optional config file include
                if (!empty($definition['config']) && is_file($definition['config'])) {
                    // Explicitly name the variable as `$router` for this service
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
        }

        // No match found at all
        throw new \InvalidArgumentException("Service '{$name}' not registered.");
    }
}