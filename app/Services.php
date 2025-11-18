<?php

namespace App;

use App\App;

/*  Simple service container / dependency injector: Loads service definitions from a config file or array and instantiates them on demand. */
class Services {
    protected array $instances          = [];
    protected array $factories          = [];

    /*  Construct the Services container. */
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

    /*  Retrieve a service instance by name, instantiating it if necessary. */
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
                $instance = $definition();

            } elseif (is_array($definition) && isset($definition['class'])) {
                $class = $definition['class'];

                $config = null;
                if (!empty($definition['config'])) {
                    if (is_string($definition['config']) && is_file($definition['config'])) {
                        $config = require $definition['config'];
                    } elseif (is_array($definition['config'])) {
                        $config = $definition['config'];
                    } else {
                        throw new \RuntimeException(
                            "Invalid config for service '{$name}': must be file path or array"
                        );
                    }

                    if (!is_array($config)) {
                        throw new \RuntimeException(
                            "Config for service '{$name}' did not return an array"
                        );
                    }
                }

                $instance = $config !== null ? new $class($config) : new $class();
            } else {
                throw new \RuntimeException("Invalid service definition for '{$name}'");
            }

            $this->instances[$name] = $instance;
            return $instance;
        } catch (\Throwable $t) {
            error_log(sprintf(
                "[Service] Failed to instantiate '%s': %s in %s:%d",
                $name,
                $t->getMessage(),
                $t->getFile(),
                $t->getLine()
            ));
            error_log($t->getTraceAsString());
            throw $t;
        }
    }
}
