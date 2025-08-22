<?php

namespace App\Router;

use App\App;
use Throwable;

/**
 * Represents a single route definition.
 *
 * Stores the HTTP method, path pattern, and handler.
 * Can match an incoming request method/URI and extract named parameters.
 */
class Route {
    public string $method;      // HTTP method (GET, POST, etc.)
    public string $path;        // Route path pattern (may contain {param} placeholders)
    public $handler;            // Callable or controller action
    public array $params = [];  // Extracted parameters from matched URI

    /**
     * @param string $method  HTTP method
     * @param string $path    Route path pattern
     * @param mixed  $handler Callable or controller action
     */
    public function __construct(string $method, string $path, $handler) {
        $this->method  = strtoupper($method);
        $this->path    = $path;
        $this->handler = $handler;
    }

    /**
     * Determine if this route matches the given method and URI.
     * Extracts named parameters if the pattern matches.
     *
     * @param string $method HTTP method of incoming request
     * @param string $uri    Request URI path
     * @return bool True if matched, false otherwise
     */
    public function matches(string $method, string $uri): bool {
        // Method must match exactly
        if ($this->method !== strtoupper($method)) {
            App::getService('logger')->warning(
                "Route method mismatch: expected {$this->method}, got {$method}",
                'router'
            );
            return false;
        }

        try {
            // Convert {param} placeholders into named regex groups
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
                App::getService('logger')->error(
                    "Failed to build regex pattern for route: {$this->path}",
                    'router'
                );
                return false;
            }

            // Attempt to match the URI against the built pattern
            if (preg_match('#^' . $pattern . '$#', $uri, $matches)) {
                // Keep only named parameters
                $this->params = array_intersect_key(
                    $matches,
                    array_flip(array_filter(array_keys($matches), 'is_string'))
                );

                App::getService('logger')->warning(
                    "Route matched: {$this->method} {$this->path} → Params: " . json_encode($this->params),
                    'router'
                );

                return true;
            }

            App::getService('logger')->warning(
                "Route pattern did not match URI: {$uri}",
                'router'
            );

            return false;
        } catch (Throwable $e) {
            App::getService('logger')->error(
                "Error matching route {$this->path}: {$e->getMessage()}",
                'router'
            );
            return false;
        }
    }
}