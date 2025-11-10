<?php

namespace App;

use App\Router\{Request, Response, Route};

/**
 * Minimal HTTP router used by the application.
 *
 * Supports simple route registration and dispatching. Routes may be registered
 * with HTTP methods and path patterns that include named captures such as
 * /books/{id:\\d+}.
 */
class Router
{
    protected array $routes = [];

    /** Register a GET route. */
    public function get(string $path, $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /** Register a POST route. */
    public function post(string $path, $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    /** Register a PUT route. */
    public function put(string $path, $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    /** Register a PATCH route. */
    public function patch(string $path, $handler): void
    {
        $this->addRoute('PATCH', $path, $handler);
    }

    /** Register a DELETE route. */
    public function delete(string $path, $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    /** Internal helper to store a route. */
    protected function addRoute(string $method, string $path, $handler): void
    {
        $this->routes[$method][] = new Route($method, $path, $handler);
    }

    /**
     * Dispatch the incoming request to the first matching route. Creates
     * Request/Response objects when not provided.
     */
    public function dispatch(?Request $request = null, ?Response $response = null): void
    {
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

    /**
     * Invoke the matched route handler. Supports "Controller@method" style
     * strings that map to Ext\Controllers\<Controller>.
     */
    protected function handle($handler, Request $request, Response $response): void
    {
        try {
            if (is_string($handler) && str_contains($handler, '@')) {
                [$class, $method] = explode('@', $handler, 2);
                $fqcn = "Ext\\Controllers\\{$class}";
                $controller = new $fqcn();
                $handler = [$controller, $method];
            }

            if (!is_callable($handler)) {
                // Non-callable handlers indicate a configuration issue.
                $response->setStatusCode(500)->setContent('Internal Server Error')->send();
                return;
            }

            $args = array_values($request->params);
            $args[] = $request;
            $args[] = $response;

            call_user_func_array($handler, $args);

        } catch (\Throwable $t) {
            // Avoid rethrowing to keep routing deterministic. Send 500.
            $response->setStatusCode(500)->setContent('Internal Server Error')->send();
        }
    }
}
