<?php

declare(strict_types=1);

namespace LoveGem\Cache;

class Repository
{
    protected array $store = [];

    protected int $defaultCacheTime = 3600;

    public function get(string $key, mixed $default = null): mixed
    {
        $item = $this->store[$key] ?? null;

        if (is_null($item)) {
            return $default;
        }

        if ($item['expires_at'] !== 0 && $item['expires_at'] < time()) {
            $this->forget($key);
            return $default;
        }

        return $item['value'];
    }

    public function put(string $key, mixed $value, int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultCacheTime;

        $this->store[$key] = [
            'value' => $value,
            'expires_at' => $ttl === 0 ? 0 : time() + $ttl,
        ];

        return true;
    }

    public function add(string $key, mixed $value, int $ttl = null): bool
    {
        if ($this->has($key)) {
            return false;
        }

        return $this->put($key, $value, $ttl);
    }

    public function forever(string $key, mixed $value): bool
    {
        return $this->put($key, $value, 0);
    }

    public function has(string $key): bool
    {
        return !is_null($this->get($key));
    }

    public function forget(string $key): bool
    {
        unset($this->store[$key]);
        return true;
    }

    public function flush(): bool
    {
        $this->store = [];
        return true;
    }

    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        $value = $this->get($key);

        if (!is_null($value)) {
            return $value;
        }

        $value = $callback();

        $this->put($key, $value, $ttl);

        return $value;
    }

    public function rememberForever(string $key, callable $callback): mixed
    {
        $value = $this->get($key);

        if (!is_null($value)) {
            return $value;
        }

        $value = $callback();

        $this->forever($key, $value);

        return $value;
    }

    public function increment(string $key, int $value = 1): int|false
    {
        if (!$this->has($key)) {
            return false;
        }

        $current = $this->get($key);
        $this->put($key, $current + $value);

        return $current + $value;
    }

    public function decrement(string $key, int $value = 1): int|false
    {
        return $this->increment($key, -$value);
    }

    public function tags(array $tags): static
    {
        return $this;
    }

    public function getDefaultCacheTime(): int
    {
        return $this->defaultCacheTime;
    }

    public function setDefaultCacheTime(int $ttl): void
    {
        $this->defaultCacheTime = $ttl;
    }
}
