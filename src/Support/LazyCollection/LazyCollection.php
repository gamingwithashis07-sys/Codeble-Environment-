<?php

declare(strict_types=1);

namespace LoveGem\Support\LazyCollection;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

class LazyCollection implements IteratorAggregate
{
    protected $source;

    public function __construct(mixed $source = null)
    {
        if (is_array($source)) {
            $this->source = function () use ($source) {
                foreach ($source as $index => $value) {
                    yield $index => $value;
                }
            };
        } elseif ($source instanceof \Closure) {
            $this->source = $source;
        } elseif ($source instanceof self) {
            $this->source = $source->source;
        } elseif ($source instanceof Traversable) {
            $this->source = function () use ($source) {
                foreach ($source as $key => $value) {
                    yield $key => $value;
                }
            };
        } else {
            $this->source = function () {
                return new \EmptyIterator();
            };
        }
    }

    public static function empty(): static
    {
        return new static();
    }

    public static function of(...$items): static
    {
        return new static($items);
    }

    public static function from($source): static
    {
        return new static($source);
    }

    public function map(callable $callback): static
    {
        return new static(function () use ($callback) {
            foreach ($this->items() as $key => $value) {
                yield $key => $callback($value, $key);
            }
        });
    }

    public function filter(callable $callback = null): static
    {
        return new static(function () use ($callback) {
            foreach ($this->items() as $key => $value) {
                if ($callback($value, $key)) {
                    yield $key => $value;
                }
            }
        });
    }

    public function each(callable $callback): void
    {
        foreach ($this->items() as $key => $value) {
            if ($callback($value, $key) === false) {
                break;
            }
        }
    }

    public function take(int $limit): static
    {
        return new static(function () use ($limit) {
            $count = 0;
            foreach ($this->items() as $key => $value) {
                if ($count >= $limit) {
                    break;
                }
                yield $key => $value;
                $count++;
            }
        });
    }

    public function skip(int $count): static
    {
        return new static(function () use ($count) {
            $current = 0;
            foreach ($this->items() as $key => $value) {
                if ($current >= $count) {
                    yield $key => $value;
                }
                $current++;
            }
        });
    }

    public function where(string $key, mixed $operator = '=', mixed $value = null): static
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        return $this->filter(function ($item) use ($key, $operator, $value) {
            $itemValue = $item[$key] ?? null;

            return match ($operator) {
                '=' => $itemValue == $value,
                '!=' => $itemValue != $value,
                '<' => $itemValue < $value,
                '<=' => $itemValue <= $value,
                '>' => $itemValue > $value,
                '>=' => $itemValue >= $value,
                default => $itemValue == $value,
            };
        });
    }

    public function flatten(): static
    {
        return new static(function () {
            foreach ($this->items() as $key => $value) {
                if (is_array($value)) {
                    foreach ((new static($value))->flatten()->items() as $innerKey => $innerValue) {
                        yield $innerKey => $innerValue;
                    }
                } else {
                    yield $key => $value;
                }
            }
        });
    }

    public function unique(string $key = null): static
    {
        return new static(function () use ($key) {
            $seen = [];
            foreach ($this->items() as $k => $value) {
                $compareKey = $key ? ($value[$key] ?? null) : $value;
                if (!in_array($compareKey, $seen)) {
                    $seen[] = $compareKey;
                    yield $k => $value;
                }
            }
        });
    }

    public function sortBy(string $key): static
    {
        $items = $this->toArray();
        usort($items, function ($a, $b) use ($key) {
            return strcmp((string)($a[$key] ?? ''), (string)($b[$key] ?? ''));
        });
        return new static($items);
    }

    public function reverse(): static
    {
        return new static(function () {
            $items = iterator_to_array($this->getIterator());
            foreach (array_reverse($items) as $key => $value) {
                yield $key => $value;
            }
        });
    }

    public function merge($collection): static
    {
        return new static(function () use ($collection) {
            yield from $this->items();
            foreach ($collection as $key => $value) {
                yield $key => $value;
            }
        });
    }

    public function toArray(): array
    {
        return iterator_to_array($this->getIterator(), false);
    }

    public function all(): array
    {
        return $this->toArray();
    }

    public function first(callable $callback = null, mixed $default = null): mixed
    {
        foreach ($this->items() as $key => $value) {
            if (!$callback || $callback($value, $key)) {
                return $value;
            }
        }
        return $default;
    }

    public function last(callable $callback = null, mixed $default = null): mixed
    {
        $result = $default;
        foreach ($this->items() as $key => $value) {
            if (!$callback || $callback($value, $key)) {
                $result = $value;
            }
        }
        return $result;
    }

    public function count(): int
    {
        $count = 0;
        foreach ($this->items() as $value) {
            $count++;
        }
        return $count;
    }

    public function isEmpty(): bool
    {
        return $this->items()->current() === null;
    }

    public function contains(mixed $value): bool
    {
        foreach ($this->items() as $item) {
            if ($item === $value) {
                return true;
            }
        }
        return false;
    }

    public function implode(string $glue = ''): string
    {
        return implode($glue, $this->toArray());
    }

    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        $accumulator = $initial;
        foreach ($this->items() as $key => $value) {
            $accumulator = $callback($accumulator, $value, $key);
        }
        return $accumulator;
    }

    public function pipe(callable $callback): mixed
    {
        return $callback($this);
    }

    public function tap(callable $callback): static
    {
        $callback($this);
        return $this;
    }

    public function chunk(int $size): static
    {
        return new static(function () use ($size) {
            $chunk = [];
            foreach ($this->items() as $key => $value) {
                $chunk[$key] = $value;
                if (count($chunk) === $size) {
                    yield new static($chunk);
                    $chunk = [];
                }
            }
            if (!empty($chunk)) {
                yield new static($chunk);
            }
        });
    }

    public function forPage(int $page, int $perPage): static
    {
        return $this->skip(($page - 1) * $perPage)->take($perPage);
    }

    public function mapToDictionary(callable $callback): static
    {
        $dictionary = [];
        foreach ($this->items() as $key => $value) {
            $pair = $callback($value, $key);
            $key = key($pair);
            $value = current($pair);
            $dictionary[$key][] = $value;
        }
        return new static($dictionary);
    }

    public function groupBy(string $key): static
    {
        return $this->mapToDictionary(function ($item) use ($key) {
            return [$item[$key] ?? $item->{$key} => $item];
        });
    }

    public function keyBy(string $key): static
    {
        $dictionary = [];
        foreach ($this->items() as $item) {
            $dictionary[$item[$key] ?? $item->{$key}] = $item;
        }
        return new static($dictionary);
    }

    public function sum(): float
    {
        return (float) $this->reduce(function ($carry, $item) {
            return $carry + $item;
        }, 0);
    }

    public function min(string $key = null): mixed
    {
        return $this->pluck($key)->toArray() ? min($this->pluck($key)->toArray()) : null;
    }

    public function max(string $key = null): mixed
    {
        return $this->pluck($key)->toArray() ? max($this->pluck($key)->toArray()) : null;
    }

    public function pluck(string $value, ?string $key = null): static
    {
        return new static(function () use ($value, $key) {
            foreach ($this->items() as $item) {
                $result = $item[$value] ?? $item->{$value};
                $itemKey = $key ? ($item[$key] ?? $item->{$key}) : null;
                yield $itemKey => $result;
            }
        });
    }

    protected function items(): \Generator
    {
        return $this->getGenerator();
    }

    protected function getGenerator(): \Generator
    {
        return $this->source();
    }

    public function getIterator(): \Traversable
    {
        return $this->items();
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
