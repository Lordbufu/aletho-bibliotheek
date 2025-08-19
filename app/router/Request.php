<?php

namespace App\Router;

class Request {
    protected string $method;
    protected string $path;
    protected array $query;
    protected array $body;
    protected array $headers;

    public function __construct() {
        $this->method  = $this->detectMethod();
        $this->path    = $this->detectPath();
        $this->query   = $_GET ?? [];
        $this->body    = $_POST ?? [];
        $this->headers = $this->detectHeaders();
    }

    protected function detectMethod(): string {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        return strtoupper($method);
    }

    protected function detectPath(): string {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        return rtrim($path, '/') ?: '/';
    }

    protected function detectHeaders(): array {
        if (function_exists('getallheaders')) {
            return getallheaders() ?: [];
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

    public function getMethod(): string {
        return $this->method;
    }

    public function getPath(): string {
        return $this->path;
    }

    public function getQuery(): array {
        return $this->query;
    }

    public function getBody(): array {
        return $this->body;
    }

    public function getHeaders(): array {
        return $this->headers;
    }

    public function input(string $key, $default = null) {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }
}