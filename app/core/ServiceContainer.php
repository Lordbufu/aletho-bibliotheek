<?php

namespace App\Core;

class ServiceContainer {
    private array $services = [];

    public function register(string $name, callable $factory): void {
        $this->services[$name] = $factory;
    }

    public function get(string $name): mixed {
        if (!isset($this->services[$name])) {
            throw new Exception("Service '$name' not registered.");
        }
        
        return $this->services[$name]();
    }
}