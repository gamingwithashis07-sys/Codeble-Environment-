<?php

declare(strict_types=1);

namespace LoveGem\Webhooks;

use Closure;

class WebhookManager
{
    protected array $webhooks = [];

    protected array $events = [];

    public function register(string $event, string $url, array $options = []): void
    {
        $this->webhooks[$event][] = [
            'url' => $url,
            'secret' => $options['secret'] ?? null,
            'headers' => $options['headers'] ?? [],
        ];
    }

    public function event(string $event, callable $callback): void
    {
        $this->events[$event] = $callback;
    }

    public function dispatch(string $event, mixed $data = []): void
    {
        foreach ($this->webhooks[$event] ?? [] as $webhook) {
            $this->send($webhook, $event, $data);
        }
    }

    protected function send(array $webhook, string $event, mixed $data): void
    {
        $payload = [
            'event' => $event,
            'data' => $data,
            'timestamp' => date('c'),
        ];

        if ($webhook['secret']) {
            $payload['signature'] = $this->generateSignature($payload, $webhook['secret']);
        }

        $headers = array_merge([
            'Content-Type' => 'application/json',
            'X-Webhook-Event' => $event,
            'X-Webhook-Timestamp' => $payload['timestamp'],
        ], $webhook['headers']);

        $this->makeRequest($webhook['url'], $payload, $headers);
    }

    protected function makeRequest(string $url, array $payload, array $headers): void
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_map(
            fn($key, $value) => "{$key}: {$value}",
            array_keys($headers),
            array_values($headers)
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        curl_exec($ch);
        curl_close($ch);
    }

    protected function generateSignature(array $payload, string $secret): string
    {
        return hash_hmac('sha256', json_encode($payload), $secret);
    }

    public function verifySignature(array $payload, string $signature, string $secret): bool
    {
        $expected = $this->generateSignature($payload, $secret);
        return hash_equals($expected, $signature);
    }

    public function getWebhooks(): array
    {
        return $this->webhooks;
    }
}
