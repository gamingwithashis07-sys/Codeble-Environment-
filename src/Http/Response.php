<?php

declare(strict_types=1);

namespace LoveGem\Http;

class Response
{
    protected string $body;

    protected int $statusCode;

    protected array $headers;

    public function __construct(string $body, int $statusCode, array $headers = [])
    {
        $this->body = $body;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function status(): int
    {
        return $this->statusCode;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function header(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    public function json(): mixed
    {
        return json_decode($this->body, true);
    }

    public function object(): mixed
    {
        return json_decode($this->body);
    }

    public function successful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function ok(): bool
    {
        return $this->statusCode === 200;
    }

    public function failed(): bool
    {
        return !$this->successful();
    }

    public function clientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    public function serverError(): bool
    {
        return $this->statusCode >= 500;
    }

    public function redirect(): bool
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    public function throw(): static
    {
        if ($this->failed()) {
            throw new \RuntimeException("HTTP {$this->statusCode}: {$this->body}");
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->body;
    }
}
