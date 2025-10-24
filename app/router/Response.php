<?php

namespace App\Router;

use App\App;

/*  HTTP Response abstraction: Encapsulates status code, headers, and content for sending a response back to the client. */
class Response {
    protected int $statusCode = 200;
    protected array $headers = [];
    protected string $content = '';

    /*  Set the HTTP status code. */
    public function setStatusCode(int $code): self {
        if ($code < 100 || $code > 599) {
            $code = 200;
        }

        $this->statusCode = $code;

        return $this;
    }

    /*  Add or replace a response header. */
    public function header(string $name, string $value): self {
        $this->headers[$name] = $value;

        return $this;
    }

    /*  Set the raw response content. */
    public function setContent(string $content): self {
        $this->content = $content;
        return $this;
    }

    /*  Set JSON response content with appropriate header and status code. */
    public function json($data, int $statusCode = 200): self {
        try {
            $json = json_encode($data, JSON_UNESCAPED_UNICODE);

            if ($json === false) {
                $error = json_last_error_msg();
                $json = '{}';
            }

            $this->setStatusCode($statusCode)
                 ->header('Content-Type', 'application/json')
                 ->setContent($json);
        } catch (\Throwable $t) {
            throw $t; 
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
        } catch (\Throwable $t) {
            throw $t;
        }
    }
}