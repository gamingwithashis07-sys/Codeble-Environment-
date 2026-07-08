<?php

declare(strict_types=1);

namespace LoveGem\Support;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;

class Fluent implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    protected array $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function all(): array
    {
        return $this->attributes;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->attributes, $key, $default);
    }

    public function set(string|array $key, mixed $value = null): void
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }
        } else {
            Arr::set($this->attributes, $key, $value);
        }
    }

    public function has(string|array $key): bool
    {
        return Arr::has($this->attributes, $key);
    }

    public function missing(string|array $key): bool
    {
        return !$this->has($key);
    }

    public function only(array|string $keys): static
    {
        return new static(Arr::only($this->attributes, (array) $keys));
    }

    public function except(array|string $keys): static
    {
        return new static(Arr::except($this->attributes, (array) $keys));
    }

    public function pull(string $key, mixed $default = null): mixed
    {
        return Arr::pull($this->attributes, $key, $default);
    }

    public function forget(string|array $keys): void
    {
        Arr::forget($this->attributes, $keys);
    }

    public function keys(): array
    {
        return array_keys($this->attributes);
    }

    public function values(): array
    {
        return array_values($this->attributes);
    }

    public function filter(callable $callback): static
    {
        return new static(array_filter($this->attributes, $callback, ARRAY_FILTER_USE_BOTH));
    }

    public function each(callable $callback): static
    {
        foreach ($this->attributes as $key => $value) {
            if ($callback($value, $key) === false) {
                break;
            }
        }
        return $this;
    }

    public function map(callable $callback): static
    {
        $keys = array_keys($this->attributes);
        $items = array_map($callback, $this->attributes, $keys);
        return new static(array_combine($keys, $items));
    }

    public function merge(array $array): static
    {
        return new static(array_merge($this->attributes, $array));
    }

    public function replace(array $array): static
    {
        return new static(array_replace($this->attributes, $array));
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    public function count(): int
    {
        return count($this->attributes);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->attributes);
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

    public function __get(string $key): mixed
    {
        return $this->get($key);
    }

    public function __set(string $key, mixed $value): void
    {
        $this->set($key, $value);
    }

    public function __isset(string $key): bool
    {
        return $this->has($key);
    }

    public function __unset(string $key): void
    {
        $this->forget($key);
    }
}
