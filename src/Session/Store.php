<?php

declare(strict_types=1);

namespace LoveGem\Session;

use LoveGem\Support\Arr;

class Store
{
    protected string $name;

    protected array $attributes = [];

    protected array $original = [];

    protected bool $started = false;

    protected array $flashData = [];

    public function __construct(string $name, array $attributes = [])
    {
        $this->name = $name;
        $this->attributes = $attributes;
        $this->original = $attributes;
    }

    public function start(): void
    {
        if ($this->started) {
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_name($this->name);
            session_start();
        }

        $this->attributes = array_merge($this->attributes, $_SESSION);
        $this->original = $this->attributes;
        $this->started = true;
    }

    public function id(): string
    {
        $this->start();
        return session_id();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->all(), $key, $default);
    }

    public function all(): array
    {
        return $this->attributes;
    }

    public function put(string|array $key, mixed $value = null): void
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->put($k, $v);
            }
            return;
        }

        Arr::set($this->attributes, $key, $value);
    }

    public function push(string $key, mixed $value): void
    {
        $array = $this->get($key, []);
        $array[] = $value;
        $this->put($key, $array);
    }

    public function prepend(string $key, mixed $value): void
    {
        $array = $this->get($key, []);
        array_unshift($array, $value);
        $this->put($key, $array);
    }

    public function has(string $key): bool
    {
        return Arr::has($this->attributes, $key);
    }

    public function exists(string $key): bool
    {
        return $this->has($key);
    }

    public function missing(string $key): bool
    {
        return !$this->has($key);
    }

    public function forget(string $key): void
    {
        Arr::forget($this->attributes, $key);
    }

    public function pull(string $key, mixed $default = null): mixed
    {
        return Arr::pull($this->attributes, $key, $default);
    }

    public function only(array|string $keys): array
    {
        return Arr::only($this->attributes, (array) $keys);
    }

    public function except(array $keys): array
    {
        return Arr::except($this->attributes, $keys);
    }

    public function flash(string $key, mixed $value): void
    {
        $this->put($key, $value);
        $this->flashData['_flash_new'][$key] = true;
    }

    public function flashInput(array $data): void
    {
        $this->flash('_old_input', $data);
    }

    public function	refurbish(): void
    {
        $this->flashData['_flash_old'] = $this->flashData['_flash_new'] ?? [];
        $this->flashData['_flash_new'] = [];
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        return $this->get($key, $default);
    }

    public function hasFlash(string $key): bool
    {
        return $this->has($key);
    }

    public function removeFlash(string $key): void
    {
        $this->forget($key);
    }

    public function clearFlash(): void
    {
        foreach ($this->flashData['_flash_old'] ?? [] as $key) {
            $this->forget($key);
        }
        $this->flashData['_flash_old'] = [];
        $this->flashData['_flash_new'] = [];
    }

    public function clear(): void
    {
        $this->attributes = [];
    }

    public function flush(): void
    {
        $this->clear();
        $this->original = [];
        $this->flashData = [];
    }

    public function destroy(): void
    {
        $this->flush();
        session_destroy();
        $this->started = false;
    }

    public function regenerate(bool $destroy = false): void
    {
        $this->start();
        session_regenerate_id($destroy);
    }

    public function token(): string
    {
        if (!$this->has('_token')) {
            $this->put('_token', bin2hex(random_bytes(32)));
        }

        return $this->get('_token');
    }

    public function CSRFToken(): string
    {
        return $this->token();
    }

    public function removeOldInput(): void
    {
        $this->forget('_old_input');
    }

    public function getOldInput(string $key = null, mixed $default = null): mixed
    {
        return Arr::get($this->get('_old_input', []), $key, $default);
    }

    public function hasOldInput(string $key = null): bool
    {
        if (is_null($key)) {
            return $this->has('_old_input');
        }

        return Arr::has($this->get('_old_input', []), $key);
    }

    public function save(): void
    {
        $this->start();
        $_SESSION = $this->attributes;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isStarted(): bool
    {
        return $this->started;
    }
}
