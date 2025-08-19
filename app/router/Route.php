<?php

namespace App\Router;

class Route {
    public string $method;
    public string $path;
    public $handler;
    public array $params = [];

    public function __construct(string $method, string $path, $handler) {
        $this->method  = strtoupper($method);
        $this->path    = $path;
        $this->handler = $handler;
    }

    public function matches(string $method, string $uri): bool {
        if ($this->method !== $method) {
            return false;
        }

        $pattern = preg_replace_callback(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)(?::([^}]+))?\}#',
            function ($m) {
                $name = $m[1];
                $regex = isset($m[2]) ? $m[2] : '[^/]+';
                return "(?P<{$name}>{$regex})";
            },
            $this->path
        );
        
        if (preg_match('#^' . $pattern . '$#', $uri, $matches)) {
            $this->params = array_intersect_key(
                $matches,
                array_flip(array_filter(array_keys($matches), 'is_string'))
            );

            return true;
        }

        return false;
    }
}