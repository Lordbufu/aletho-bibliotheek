<?php

namespace App;

use App\Router\{Request, Response, Route};

/**
 * Router service for registering and dispatching HTTP routes.
 *
 * This class maintains a registry of routes and matches incoming requests
 * against them. Routes can be loaded declaratively via `loadRoutes()` from
 * a configuration file, or added programmatically using `addRoute()`.
 *
 * Responsibilities:
 * - Store route definitions keyed by HTTP method.
 * - Match requests to routes and invoke the appropriate handler.
 * - Support controller@method string handlers by instantiating controllers.
 * - Provide error handling and logging for failed dispatches.
 *
 * Usage:
 *   $router = new Router();
 *   $router->loadRoutes(require 'routes.php');
 *   $router->dispatch();
 */
class Router {
    /** @var array<string, Route[]> Registered routes grouped by HTTP method */
    protected array $routes = [];

    /** Register a single route for the given HTTP method, path, and handler. */
    private function addRoute(string $method, string $path, $handler): void {
        $this->routes[$method][] = new Route($method, $path, $handler);
    }

    /** Resolve and invoke a route handler, handling controller@method strings. */
    private function handle($handler, Request $request, Response $response): void {
        try {
            if (is_string($handler) && str_contains($handler, '@')) {
                [$class, $method] = explode('@', $handler, 2);
                $fqcn = "Ext\\Controllers\\{$class}";
                $controller = new $fqcn();
                $handler = [$controller, $method];
            }

            if (!is_callable($handler)) {
                $response->setStatusCode(500)->setContent('Internal Server Error')->send();
                return;
            }

            $args = array_values($request->params);
            $args[] = $request;
            $args[] = $response;

            call_user_func_array($handler, $args);

        } catch (\Throwable $t) {
            error_log(sprintf(
                '[Router] Controller instantiation failed: %s in %s:%d',
                $t->getMessage(),
                $t->getFile(),
                $t->getLine()
            ));
            error_log('[Router] Trace: ' . $t->getTraceAsString());

            $response->setStatusCode(500)->setContent('Internal Server Error')->send();
        }
    }

    /** Bulk‑load routes from a declarative array of [method, path, handler]. */
    public function loadRoutes(array $routes): void {
        foreach ($routes as [$method, $path, $handler]) {
            $this->addRoute(strtoupper($method), $path, $handler);
        }
    }

    /** Match the current request against registered routes and execute its handler. */
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
}