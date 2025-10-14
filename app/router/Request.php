<?php

namespace App\Router;

use App\App;
use Throwable;

/** HTTP Request abstraction.
 *  Encapsulates HTTP method, path, query parameters, body data, and headers for use by the router and controllers.
 */
class Request {
    protected string    $method;
    protected string    $path;
    protected array     $query;
    protected array     $body;
    protected array     $headers;
    // TODO: consider setter/getter for params if we want stricter encapsulation
    public array $params = [];

    /*  Build a Request object from PHP superglobals. */
    public function __construct() {
        try {
            $this->method  = $this->detectMethod();
            $this->path    = $this->detectPath();
            $this->query   = $_GET ?? [];
            $this->body    = $_POST ?? [];
            $this->headers = $this->detectHeaders();
        } catch (Throwable $e) {
            App::getServiceSafeLogger()->error(
                "Failed to initialize Request: {$e->getMessage()}",
                'router'
            );

            $this->method  = 'GET';
            $this->path    = '/';
            $this->query   = [];
            $this->body    = [];
            $this->headers = [];
        }
    }

    /** Detect the HTTP method, supporting method override via _method POST param.
     *      @return string  -> The HTTP method.
     */
    protected function detectMethod(): string {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        return strtoupper($method);
    }

    /** Detect and normalize the request path from the URI.
     *      @return string  -> The normalized path.
     */
    protected function detectPath(): string {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        if (!isset($_SERVER['REQUEST_URI'])) {
            App::getService('logger')->warning(
                "REQUEST_URI not set, defaulting to '/'",
                'request'
            );
        }

        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        return rtrim($path, '/') ?: '/';
    }

    /** Detect HTTP request headers.
     *      @return arrray  -> The HTTP headers.
     */
    protected function detectHeaders(): array {
        if (function_exists('getallheaders')) {
            $headers = getallheaders() ?: [];

            if (empty($headers)) {
                App::getService('logger')->warning(
                    "No HTTP headers detected",
                    'request'
                );
            }

            return $headers;
        }

        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', substr($key, 5));
                $headers[$name] = $value;
            }
        }

        if (empty($headers)) {
            App::getService('logger')->warning(
                "No HTTP headers detected via fallback",
                'router'
            );
        }

        return $headers;
    }

    /** Get the HTTP method.
     *      @return string  -> The HTTP method.
     */
    public function getMethod(): string {
        return $this->method;
    }

    /** Get the normalized request path.
     *      @return string  -> The normalized path.
     */
    public function getPath(): string {
        return $this->path;
    }

    /** Get query string parameters.
     *      @return array  -> The query parameters.
     */   
    public function getQuery(): array {
        return $this->query;
    }

    /** Get POST/PUT body parameters.
     *      @return array  -> The body parameters.
     */
    public function getBody(): array {
        return $this->body;
    }

    /** Get HTTP request headers.
     *      @return array  -> The headers.
     */
    public function getHeaders(): array {
        return $this->headers;
    }

    /** Retrieve a single input value from body or query parameters.
     *      @param string $key      -> Parameter name
     *      @param mixed  $default  -> Default value if not found
     *      @return mixed
     */
    public function input(string $key, $default = null) {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }
}