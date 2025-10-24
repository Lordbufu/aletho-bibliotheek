<?php

namespace App\Router;

use App\App;

/*  HTTP Request abstraction: Encapsulates HTTP method, path, query parameters, body data, and headers for use by the router and controllers. */
class Request {
    protected string    $method;
    protected string    $path;
    protected array     $query;
    protected array     $body;
    protected array     $headers;
    public array $params = [];

    /*  Build a Request object from PHP superglobals. */
    public function __construct() {
        try {
            $this->method  = $this->detectMethod();
            $this->path    = $this->detectPath();
            $this->query   = $_GET ?? [];
            $this->body    = $_POST ?? [];
            $this->headers = $this->detectHeaders();
        } catch (\Throwable $t) {
            throw $t;
            $this->method  = 'GET';
            $this->path    = '/';
            $this->query   = [];
            $this->body    = [];
            $this->headers = [];
        }
    }

    /*  Detect the HTTP method, supporting method override via _method POST param. */
    protected function detectMethod(): string {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        return strtoupper($method);
    }

    /*  Detect and normalize the request path from the URI. */
    protected function detectPath(): string {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        return rtrim($path, '/') ?: '/';
    }

    /*  Detect HTTP request headers. */
    protected function detectHeaders(): array {
        if (function_exists('getallheaders')) {
            $headers = getallheaders() ?: [];
            return $headers;
        }

        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', substr($key, 5));
                $headers[$name] = $value;
            }
        }

        return $headers;
    }

    /*  Get the HTTP method. */
    public function getMethod(): string {
        return $this->method;
    }

    /*  Get the normalized request path. */
    public function getPath(): string {
        return $this->path;
    }

    /*  Get query string parameters. */   
    public function getQuery(): array {
        return $this->query;
    }

    /*  Get POST/PUT body parameters. */
    public function getBody(): array {
        return $this->body;
    }

    /*  Get HTTP request headers. */
    public function getHeaders(): array {
        return $this->headers;
    }

    /*  Retrieve a single input value from body or query parameters. */
    public function input(string $key, $default = null) {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }
}