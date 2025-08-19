<?php

namespace App;

use App\Router\{Request, Response, Route};

class Router {
    protected array $routes = [];

    public function get(string $path, $handler): void {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, $handler): void {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, $handler): void {
        $this->addRoute('PUT', $path, $handler);
    }

    public function patch(string $path, $handler): void {
        $this->addRoute('PATCH', $path, $handler);
    }

    public function delete(string $path, $handler): void {
        $this->addRoute('DELETE', $path, $handler);
    }

    protected function addRoute(string $method, string $path, $handler): void {
        $this->routes[$method][] = new Route($method, $path, $handler);
    }

    public function dispatch(?Request $request = null, ?Response $response = null): void {
        $request  ??= new Request();
        $response ??= new Response();

        foreach ($this->routes[$request->getMethod()] ?? [] as $route) {
            if ($route->matches($request->getMethod(), $request->getPath())) {
                $request->params = $route->params;
                $this->handle($route->handler, $request, $response);
                return;
            }
        }

        $response->setStatusCode(404)->setContent('Not Found')->send();
        return;
    }

    protected function handle($handler, Request $request, Response $response): void {
        if (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler, 2);
            $fqcn = "ext\\controllers\\{$class}";
            $controller = new $fqcn();
            $handler = [$controller, $method];
        }

        if (!is_callable($handler)) {
            throw new \RuntimeException('Handler is not callable');
        }

        $args = array_values($request->params);
        $args[] = $request;
        $args[] = $response;

        call_user_func_array($handler, $args);
    }
}