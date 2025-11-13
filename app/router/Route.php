<?php

namespace App\Router;

/**
 * Represents a single route definition. Stores HTTP method, path pattern and
 * handler. Capable of matching an incoming method+URI and extracting named
 * parameters.
 */
class Route {
    public string $method;
    public string $path;
    public $handler;
    public array $params = [];

    /**
     * @param string $method HTTP method (GET, POST, ...)
     * @param string $path   Route path pattern (e.g. /books/{id:\\d+})
     * @param callable|string $handler Controller handler or callable
     */
    public function __construct(string $method, string $path, $handler) {
        $this->method  = strtoupper($method);
        $this->path    = $path;
        $this->handler = $handler;
    }

    /**
     * Check whether this route matches the provided method and URI.
     * If matched, $this->params will contain named regex captures.
     */
    public function matches(string $method, string $uri): bool {
        if ($this->method !== strtoupper($method)) {
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

        if ($pattern === null) {
            return false;
        }

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