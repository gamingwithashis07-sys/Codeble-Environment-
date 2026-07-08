<?php

declare(strict_types=1);

namespace LoveGem\Config;

use LoveGem\Support\Arr;

class Repository
{
    protected array $config = [];

    protected array $stale = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function all(): array
    {
        return $this->config;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (Arr::has($this->config, $key)) {
            return Arr::get($this->config, $key);
        }

        return $default;
    }

    public function set(string $key, mixed $value = null): void
    {
        $keys = explode('.', $key);

        $config = &$this->config;

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($config[$key]) || !is_array($config[$key])) {
                $config[$key] = [];
            }

            $config = &$config[$key];
        }

        $config[array_shift($keys)] = $value;
    }

    public function has(string $key): bool
    {
        return Arr::has($this->config, $key);
    }

    public function forget(string $key): void
    {
        Arr::forget($this->config, $key);
    }

    public function prepend(string $key, mixed $value): void
    {
        $array = $this->get($key, []);

        array_unshift($array, $value);

        $this->set($key, $array);
    }

    public function push(string $key, mixed $value): void
    {
        $array = $this->get($key, []);

        $array[] = $value;

        $this->set($key, $array);
    }

    public function pull(string $key, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);

        $this->forget($key);

        return $value;
    }

    public function toArray(): array
    {
        return $this->config;
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->forget($offset);
    }
}
