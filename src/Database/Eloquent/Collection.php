<?php

declare(strict_types=1);

namespace LoveGem\Database\Eloquent;

class Collection implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable
{
    protected array $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public static function make(array $items = []): static
    {
        return new static($items);
    }

    public function all(): array
    {
        return $this->items;
    }

    public function first(callable $callback = null, mixed $default = null): mixed
    {
        if (is_null($callback)) {
            return $this->items[0] ?? $default;
        }

        foreach ($this->items as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return $default;
    }

    public function last(callable $callback = null, mixed $default = null): mixed
    {
        if (is_null($callback)) {
            return end($this->items) ?: $default;
        }

        return $this->filter($callback)->last() ?? $default;
    }

    public function filter(callable $callback = null): static
    {
        if ($callback) {
            return new static(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
        }

        return new static(array_filter($this->items));
    }

    public function where(string $key, mixed $value = null): static
    {
        return $this->filter(function ($item) use ($key, $value) {
            $result = is_array($item) ? ($item[$key] ?? null) : ($item->{$key} ?? null);
            return is_null($value) ? !is_null($result) : $result === $value;
        });
    }

    public function whereIn(string $key, array $values): static
    {
        return $this->filter(function ($item) use ($key, $values) {
            $result = is_array($item) ? ($item[$key] ?? null) : ($item->{$key} ?? null);
            return in_array($result, $values);
        });
    }

    public function sortBy(string $key): static
    {
        $results = $this->items;
        uasort($results, fn($a, $b) => strcmp((string)($a[$key] ?? ''), (string)($b[$key] ?? '')));
        return new static(array_values($results));
    }

    public function map(callable $callback): static
    {
        $keys = array_keys($this->items);
        $items = array_map($callback, $this->items, $keys);
        return new static(array_combine($keys, $items));
    }

    public function each(callable $callback): static
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }
        return $this;
    }

    public function pluck(string $key): static
    {
        return new static(array_map(fn($item) => $item[$key] ?? $item->{$key} ?? null, $this->items));
    }

    public function values(): static
    {
        return new static(array_values($this->items));
    }

    public function keys(): static
    {
        return new static(array_keys($this->items));
    }

    public function only(array $keys): static
    {
        return $this->filter(fn($item, $key) => in_array($key, $keys));
    }

    public function except(array $keys): static
    {
        return $this->filter(fn($item, $key) => !in_array($key, $keys));
    }

    public function implode(string $value, string $glue = ''): string
    {
        return implode($glue, $this->pluck($value)->all());
    }

    public function sum(callable|string $callback = null): float
    {
        if (is_null($callback)) {
            return array_sum($this->items);
        }
        return $this->map($callback)->sum();
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    public function toArray(): array
    {
        return $this->items;
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

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->items);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }
}
