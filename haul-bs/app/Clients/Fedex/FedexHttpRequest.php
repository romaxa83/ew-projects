<?php

namespace App\Clients\Fedex;

class FedexHttpRequest
{
    protected string $path;
    protected mixed $body;
    protected string $method;

    protected array $headers;

    public function __construct($path, $method)
    {
        $this->path = $path;
        $this->method = $method;
        $this->body = null;
        $this->headers = $this->baseHeaders();
    }

    protected function baseHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    public function setHeaders(array $headers): void
    {
        $this->headers = array_merge($this->headers, $headers);
    }

    public function setBody($body): void
    {
        $this->body = $body;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getBody(): array|string|null
    {
        return $this->body;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}
