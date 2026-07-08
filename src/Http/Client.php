<?php

declare(strict_types=1);

namespace LoveGem\Http;

use Closure;

class Client
{
    protected array $headers = [];

    protected array $options = [];

    protected ?string $baseUrl = null;

    protected int $timeout = 30;

    protected bool $verify = true;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public static function withOptions(array $options): static
    {
        return new static($options);
    }

    public function timeout(int $seconds): static
    {
        $this->timeout = $seconds;
        return $this;
    }

    public function baseUrl(string $url): static
    {
        $this->baseUrl = rtrim($url, '/');
        return $this;
    }

    public function withHeaders(array $headers): static
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    public function withoutVerifying(): static
    {
        $this->verify = false;
        return $this;
    }

    public function attach(array|string $files, ?string $name = null, ?array $meta = null): static
    {
        if (is_string($files)) {
            $this->attachments[] = [
                'name' => $name ?? basename($files),
                'file' => $files,
                'meta' => $meta,
            ];
        } else {
            foreach ($files as $file) {
                $this->attachments[] = [
                    'name' => $name ?? basename($file),
                    'file' => $file,
                    'meta' => $meta,
                ];
            }
        }
        return $this;
    }

    public function get(string $url, array $query = []): Response
    {
        return $this->request('GET', $url, ['query' => $query]);
    }

    public function post(string $url, mixed $data = []): Response
    {
        return $this->request('POST', $url, ['body' => $data]);
    }

    public function put(string $url, mixed $data = []): Response
    {
        return $this->request('PUT', $url, ['body' => $data]);
    }

    public function patch(string $url, mixed $data = []): Response
    {
        return $this->request('PATCH', $url, ['body' => $data]);
    }

    public function delete(string $url, array $data = []): Response
    {
        return $this->request('DELETE', $url, ['body' => $data]);
    }

    public function head(string $url, array $query = []): Response
    {
        return $this->request('HEAD', $url, ['query' => $query]);
    }

    public function options(string $url, array $query = []): Response
    {
        return $this->request('OPTIONS', $url, ['query' => $query]);
    }

    public function request(string $method, string $url, array $options = []): Response
    {
        $url = $this->buildUrl($url, $options['query'] ?? []);

        $ch = curl_init();

        $options = array_merge($this->options, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_SSL_VERIFYPEER => $this->verify,
            CURLOPT_HTTPHEADER => $this->buildHeaders(),
        ]);

        if (isset($options['body'])) {
            $options[CURLOPT_POSTFIELDS] = is_array($options['body'])
                ? json_encode($options['body'])
                : $options['body'];

            if (isset($this->headers['Content-Type']) && $this->headers['Content-Type'] === 'application/json') {
                $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
            }
        }

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException("cURL Error: {$error}");
        }

        return new Response($response, $statusCode, $this->headers);
    }

    protected function buildUrl(string $url, array $query): string
    {
        if ($this->baseUrl) {
            $url = $this->baseUrl . '/' . ltrim($url, '/');
        }

        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }

    protected function buildHeaders(): array
    {
        $headers = [];

        foreach ($this->headers as $name => $value) {
            $headers[] = "{$name}: {$value}";
        }

        return $headers;
    }

    public function async(): PendingRequest
    {
        return new PendingRequest($this);
    }
}
