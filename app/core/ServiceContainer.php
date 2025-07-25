<?php

namespace App\Core;

use RuntimeException;

class ServiceContainer {
    /** @var array<string,callable> */
    private array $factories = [];

    /** @var array<string,mixed> */
    private array $instances = [];

    /**
     * @param bool $shared if true, the factory is only invoked once
     */
    public function register(string $key, callable $factory, bool $shared = true): void {
        $this->factories[$key] = $factory;

        if($shared) {
            // mark for lazy, singleton creation
            $this->instances[$key] = null;
        }
    }

    public function get(string $key): mixed {
        if(! array_key_exists($key, $this->factories)) {
            throw new RuntimeException("Service '$key' not registered.");
        }

        // if shared, return cached instance (or build once)
        if(array_key_exists($key, $this->instances)) {
            if($this->instances[$key] === null) {
                $this->instances[$key] = ($this->factories[$key])();
            }

            return $this->instances[$key];
        }

        // non‐shared: always invoke a fresh one
        return ($this->factories[$key])();
    }
}