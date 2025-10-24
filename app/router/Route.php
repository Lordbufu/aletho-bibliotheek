<?php

namespace App\Router;

use App\App;

/*  Represents a single route definition: stores the HTTP method, path pattern, and handler, can match an incoming request method/URI and extract named parameters. */
class Route {
    public string $method;
    public string $path;
    public $handler;
    public array $params = [];

    /*  Class constructor, ensuring the expected variable are populated. */
    public function __construct(string $method, string $path, $handler) {
        $this->method  = strtoupper($method);
        $this->path    = $path;
        $this->handler = $handler;
    }

    /*  Determine if this route matches the given method and URI. */
    public function matches(string $method, string $uri): bool {
        if ($this->method !== strtoupper($method)) {
            return false;
        }

        try {
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
        } catch (\Throwable $t) {
            throw $t;
            return false;
        }
    }
}