<?php

namespace App\Router;

class Request {
    protected string $method;
    protected string $path;
    protected array $query;
    protected array $body;
    protected array $headers;
    public array $params = [];

    /** Initialize request by detecting method, path, query, body, headers. */
    public function __construct() {
        $this->method  = $this->detectMethod();
        $this->path    = $this->detectPath();
        $this->query   = $_GET ?? [];
        $this->body    = $_POST ?? [];
        $this->headers = $this->detectHeaders();
    }

    /** Detect the HTTP method. Supports _method override in form POST bodies. */
    protected function detectMethod(): string {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper((string) $_POST['_method']);
        }

        return strtoupper($method);
    }

    /** Detect and normalize request path (no trailing slash, root is '/'). */
    protected function detectPath(): string {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        return rtrim($path, '/') ?: '/';
    }
    
    /** Detect request headers into an associative array. */
    protected function detectHeaders(): array {
        if (function_exists('getallheaders')) {
            return getallheaders() ?: [];
        }

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $name = str_replace('_', '-', substr($key, 5));
                $headers[$name] = $value;
            }
        }

        return $headers;
    }

    /** Return HTTP method (GET|POST|PUT|... ). */
    public function getMethod(): string {
        return $this->method;
    }

    /** Return normalized request path. */
    public function getPath(): string {
        return $this->path;
    }

    /** Return query parameters as an array. */
    public function getQuery(): array {
        return $this->query;
    }

    /** Return POST/PUT body parameters as an array. */
    public function getBody(): array {
        return $this->body;
    }

    /** Return the parsed request headers. */
    public function getHeaders(): array {
        return $this->headers;
    }

    /** Retrieve a specific input value from body or query with optional default. */
    public function input(string $key, $default = null) {
        if (array_key_exists($key, $this->body)) {
            return $this->body[$key];
        }

        if (array_key_exists($key, $this->query)) {
            return $this->query[$key];
        }

        return $default;
    }
}