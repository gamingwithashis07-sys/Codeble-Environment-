<?php

declare(strict_types=1);

namespace LoveGem\Broadcasting;

use Closure;

class Broadcaster
{
    protected array $channels = [];

    protected array $events = [];

    protected array $sockets = [];

    public function channel(string $name, callable $callback): void
    {
        $this->channels[$name] = $callback;
    }

    public function presence(string $name, callable $callback): void
    {
        $this->channels[$name] = [
            'type' => 'presence',
            'callback' => $callback,
        ];
    }

    public function broadcast(array $channels, string $event, mixed $data = []): void
    {
        foreach ($channels as $channel) {
            $this->broadcastToChannel($channel, $event, $data);
        }
    }

    protected function broadcastToChannel(string $channel, string $event, mixed $data): void
    {
        $payload = [
            'channel' => $channel,
            'event' => $event,
            'data' => $data,
            'socket' => $this->sockets[$channel] ?? null,
        ];

        $this->events[] = $payload;

        $this->sendViaDriver($payload);
    }

    protected function sendViaDriver(array $payload): void
    {
        $driver = $this->getDriver();

        match ($driver) {
            'pusher' => $this->sendViaPusher($payload),
            'redis' => $this->sendViaRedis($payload),
            'log' => $this->sendViaLog($payload),
            default => $this->sendViaLog($payload),
        };
    }

    protected function sendViaPusher(array $payload): void
    {
        // Pusher implementation
    }

    protected function sendViaRedis(array $payload): void
    {
        // Redis implementation
    }

    protected function sendViaLog(array $payload): void
    {
        $logger = app('log');
        if ($logger) {
            $logger->info('Broadcasting event', $payload);
        }
    }

    public function getDriver(): string
    {
        return app('config')->get('broadcasting.default', 'log');
    }

    public function getChannels(): array
    {
        return $this->channels;
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function socket(string $channel, string $socket): void
    {
        $this->sockets[$channel] = $socket;
    }

    public function auth(string $channelName): array
    {
        $channel = $this->channels[$channelName] ?? null;

        if (!$channel) {
            throw new \RuntimeException("Channel [{$channelName}] not defined.");
        }

        if (is_array($channel) && isset($channel['callback'])) {
            $callback = $channel['callback'];
        } else {
            $callback = $channel;
        }

        return $callback();
    }
}
