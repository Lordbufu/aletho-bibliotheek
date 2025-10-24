<?php

namespace App;

use App\Router\{Request, Response, Route};

/*  Simple HTTP Router: Registers routes for various HTTP methods and dispatches incoming requests to the appropriate handler. */
class Router {
    protected array $routes = [];

    /*  Register a GET route. */
    public function get(string $path, $handler): void {
        $this->addRoute('GET', $path, $handler);
    }

    /*  Register a POST route. */
    public function post(string $path, $handler): void {
        $this->addRoute('POST', $path, $handler);
    }

    /*  Register a PUT route. */
    public function put(string $path, $handler): void {
        $this->addRoute('PUT', $path, $handler);
    }

    /*  Register a PATCH route. */
    public function patch(string $path, $handler): void {
        $this->addRoute('PATCH', $path, $handler);
    }

    /*  Register a DELETE route. */
    public function delete(string $path, $handler): void {
        $this->addRoute('DELETE', $path, $handler);
    }

    /*  Internal helper to store a route. */
    protected function addRoute(string $method, string $path, $handler): void {
        $this->routes[$method][] = new Route($method, $path, $handler);
    }

    /*  Dispatch the incoming request to the first matching route. */
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
    }

    /*  Invoke the matched route handler. */
    protected function handle($handler, Request $request, Response $response): void {
        try {
            if (is_string($handler) && str_contains($handler, '@')) {
                [$class, $method] = explode('@', $handler, 2);
                $fqcn = "Ext\\Controllers\\{$class}";
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

        } catch (\Throwable $t) {
            throw $t;         
            $response->setStatusCode(500)->setContent('Internal Server Error')->send();
        }
    }
}
