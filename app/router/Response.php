<?php

namespace App\Router;

use App\App;
use Throwable;

/** HTTP Response abstraction.
 *  Encapsulates status code, headers, and content for sending a response back to the client.
 */
class Response {
    protected int $statusCode = 200;
    protected array $headers = [];
    protected string $content = '';

    /** Set the HTTP status code.
     *      @param int $code -> HTTP status code
     *      @return self
     */
    public function setStatusCode(int $code): self {
        if ($code < 100 || $code > 599) {
            App::getService('logger')->warning(
                "Invalid HTTP status code: {$code}, defaulting to 200",
                'router'
            );
            $code = 200;
        }

        $this->statusCode = $code;

        return $this;
    }

    /** Add or replace a response header.
     *      @param string $name  -> Header name
     *      @param string $value -> Header value
     *      @return self
     */
    public function header(string $name, string $value): self {
        $this->headers[$name] = $value;

        return $this;
    }

    /** Set the raw response content.
     *      @param string $content -> Response body content
     *      @return self
     */
    public function setContent(string $content): self {
        if ($content === '') {
            App::getService('logger')->warning(
                "Setting empty response content",
                'router'
            );
        }

        $this->content = $content;

        return $this;
    }

    /** Set JSON response content with appropriate header and status code.
     *      @param mixed $data      -> Data to encode as JSON
     *      @param int $statusCode  -> HTTP status code, defaults to 200
     *      @return self
     */
    public function json($data, int $statusCode = 200): self {
        try {
            $json = json_encode($data, JSON_UNESCAPED_UNICODE);

            if ($json === false) {
                $error = json_last_error_msg();
                App::getService('logger')->error(
                    "JSON encoding failed: {$error}",
                    'router'
                );
                $json = '{}';
            }

            $this->setStatusCode($statusCode)
                 ->header('Content-Type', 'application/json')
                 ->setContent($json);
        } catch (Throwable $e) {
            App::getService('logger')->error(
                "Unexpected error encoding JSON: {$e->getMessage()}",
                'router'
            );

            $this->setStatusCode(500)
                 ->header('Content-Type', 'application/json')
                 ->setContent('{"error":"Internal Server Error"}');
        }
        return $this;
    }

    /** Send the response to the client. */
    public function send(): void {
        try {
            http_response_code($this->statusCode);

            foreach ($this->headers as $name => $value) {
                header("{$name}: {$value}");
            }

            echo $this->content;

            App::getService('logger')->warning(
                "Response sent [{$this->statusCode}], " .
                count($this->headers) . " headers, " .
                strlen($this->content) . " bytes",
                'router'
            );

        } catch (Throwable $e) {
            App::getService('logger')->error(
                "Failed to send response: {$e->getMessage()}",
                'router'
            );
        }
    }
}