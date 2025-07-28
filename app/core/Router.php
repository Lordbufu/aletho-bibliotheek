<?php
namespace App\Core;

class Router {
    protected array $routes = [];

    public function get(string $path, callable $handler): void {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable $handler): void {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $requestUri = null, string $requestMethod = null) {
        $requestUri = $requestUri ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $requestMethod = $requestMethod ?? $_SERVER['REQUEST_METHOD'];

        $routes = $this->routes[$requestMethod] ?? [];
        foreach ($routes as $path => $handler) {
            if ($path === $requestUri) {
                return call_user_func($handler);
            }
        }
        
        http_response_code(404);
        echo '404 Not Found';
        return null;
    }
}
