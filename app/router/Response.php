<?php

namespace App\Router;

/**
 * Small HTTP Response helper.
 * Stores status code, headers and content and sends them when requested.
 */
class Response
{
    protected int $statusCode = 200;
    protected array $headers = [];
    protected string $content = '';

    /** Set the HTTP status code (defaults to 200 when out of range). */
    public function setStatusCode(int $code): self
    {
        if ($code < 100 || $code > 599) {
            $code = 200;
        }

        $this->statusCode = $code;

        return $this;
    }

    /** Add or replace a response header. */
    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /** Set raw response content. */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Set JSON response content and Content-Type header. When encoding
     * fails we set a minimal JSON error payload and status 500.
     *
     * @param mixed $data
     */
    public function json($data, int $statusCode = 200): self
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            // Encoding failed — return a safe generic payload.
            $this->setStatusCode(500)
                 ->header('Content-Type', 'application/json')
                 ->setContent('{"error":"Internal Server Error"}');
            return $this;
        }

        $this->setStatusCode($statusCode)
             ->header('Content-Type', 'application/json')
             ->setContent($json);

        return $this;
    }

    /**
     * Send status, headers and body to the client.
     */
    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        echo $this->content;
    }
}