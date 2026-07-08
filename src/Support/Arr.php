<?php

declare(strict_types=1);

namespace LoveGem\Support;

use ArrayAccess;

class Arr
{
    public static function add(array $array, string|int|null $key, mixed $value): array
    {
        if (is_null($key)) {
            return array_merge($array, $value);
        }

        $array[$key] = $value;

        return $array;
    }

    public static function collapse(array $array): array
    {
        $results = [];

        foreach ($array as $values) {
            if (is_array($values) && array_is_list($values)) {
                array_push($results, ...$values);
            } else {
                $results[] = $values;
            }
        }

        return $results;
    }

    public static function divide(array $array): array
    {
        return [array_keys($array), array_values($array)];
    }

    public static function dot(array $array, string $prepend = ''): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge($results, static::dot($value, $prepend.$key.'.'));
            } else {
                $results[$prepend.$key] = $value;
            }
        }

        return $results;
    }

    public static function except(array $array, array|string|null $keys = null): array
    {
        if (is_null($keys)) {
            return $array;
        }

        $keys = (array) $keys;

        if (empty($keys)) {
            return $array;
        }

        $results = [];

        foreach ($array as $key => $value) {
            if (!in_array($key, $keys)) {
                $results[$key] = $value;
            }
        }

        return $results;
    }

    public static function exists(ArrayAccess|array $array, string|int $key): bool
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }

    public static function first(array $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if (is_null($callback)) {
            if (empty($array)) {
                return $default;
            }

            return reset($array);
        }

        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return $default;
    }

    public static function last(array $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if (is_null($callback)) {
            return empty($array) ? $default : end($array);
        }

        return static::first(array_reverse($array, true), $callback, $default);
    }

    public static function flatten(array $array, int $depth = INF): array
    {
        $result = [];

        foreach ($array as $item) {
            if (!is_array($item)) {
                $result[] = $item;
            } elseif ($depth === 1) {
                $result = array_merge($result, array_values($item));
            } else {
                $result = array_merge($result, static::flatten($item, $depth - 1));
            }
        }

        return $result;
    }

    public static function get(ArrayAccess|array $array, string|int|null $key, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return $array;
        }

        if (static::exists($array, $key)) {
            return $array[$key];
        }

        foreach (explode('.', (string) $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } elseif ($array instanceof ArrayAccess && $array->offsetExists($segment)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    public static function has(ArrayAccess|array $array, string|array|null $keys): bool
    {
        if (is_null($keys)) {
            return false;
        }

        $keys = (array) $keys;

        if (!$array) {
            return false;
        }

        if ($keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            $subKeyDefault = null;

            if (str_contains($key, '.')) {
                [$subKey, $rest] = explode('.', $key, 2);

                $subArray = static::get($array, $subKey, $subKeyDefault);

                return static::has($subArray, $rest);
            }

            if (!static::exists($array, $key)) {
                return false;
            }

            $array = $array[$key];
        }

        return true;
    }

    public static function only(array $array, array|string|null $keys = null): array
    {
        if (is_null($keys)) {
            return $array;
        }

        $keys = (array) $keys;

        if (empty($keys)) {
            return $array;
        }

        $results = [];

        foreach ($array as $key => $value) {
            if (in_array($key, $keys)) {
                $results[$key] = $value;
            }
        }

        return $results;
    }

    public static function pluck(array $array, string|array $value, ?string $key = null): array
    {
        $results = [];

        [$value, $key] = static::explodePluckParameters($value, $key);

        foreach ($array as $item) {
            $itemValue = data_get($item, $value);

            if (is_null($item)) {
                continue;
            }

            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = data_get($item, $key);
                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }

    protected static function explodePluckParameters(string|array $value, ?string $key): array
    {
        $value = is_string($value) ? explode('.', $value) : $value;

        $key = is_null($key)
            ? null
            : (is_array($key) ? $key : explode('.', $key));

        return [$value, $key];
    }

    public static function pull(array &$array, string|int|null $key, mixed $default = null): mixed
    {
        $value = static::get($array, $key, $default);

        static::forget($array, $key);

        return $value;
    }

    public static function set(ArrayAccess|array &$array, string|int|null $key, mixed $value): array
    {
        if (is_null($key)) {
            if (is_array($value)) {
                return $array = $value;
            }

            throw new InvalidArgumentException('Value must be an array when setting null key.');
        }

        $keys = explode('.', (string) $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    public static function sort(array $array, ?callable $callback = null): array
    {
        if (is_null($callback)) {
            asort($array);
        } else {
            uasort($array, $callback);
        }

        return $array;
    }

    public static function sortRecursive(array $array, int $sortFlags = SORT_REGULAR): array
    {
        foreach ($array as &$item) {
            if (is_array($item)) {
                $item = static::sortRecursive($item, $sortFlags);
            }
        }

        if (static::isMultidimensional($array)) {
            sort($array);
        } else {
            asort($array);
        }

        return $array;
    }

    protected static function isMultidimensional(array $array): bool
    {
        return count(array_filter($array, 'is_array')) > 0;
    }

    public static function where(array $array, callable $callback): array
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    public static function whereKey(array $array, array|string|int $keys): array
    {
        $keys = (array) $keys;

        return array_filter(
            $array,
            fn ($value, $key) => in_array($key, $keys),
            ARRAY_FILTER_USE_BOTH
        );
    }

    public static function wrap(mixed $value): array
    {
        return is_array($value) ? $value : [$value];
    }

    public static function unwrap(mixed $value): array
    {
        return is_array($value) ? reset($value) : $value;
    }

    public static function map(array $array, callable $callback): array
    {
        $keys = array_keys($array);
        $values = array_map($callback, $array, $keys);

        return array_combine($keys, $values);
    }

    public static function mapWithKeys(array $array, callable $callback): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            $pair = $callback($value, $key);

            foreach ($pair as $mapKey => $mapValue) {
                $results[$mapKey] = $mapValue;
            }
        }

        return $results;
    }

    public static function forPage(array $array, int $page, int $perPage): array
    {
        return array_slice($array, ($page - 1) * $perPage, $perPage);
    }

    public static function chunk(array $array, int $size, callable $callback): array
    {
        $chunks = array_chunk($array, $size);

        foreach ($chunks as $chunk) {
            $callback($chunk);
        }

        return $chunks;
    }

    public static function dataGet(mixed $target, string|array|null $key, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        foreach ($key as $segment) {
            if (is_array($target) && array_key_exists($segment, $target)) {
                $target = $target[$segment];
            } elseif ($target instanceof \ArrayAccess && $target->offsetExists($segment)) {
                $target = $target[$segment];
            } else {
                return $default;
            }
        }

        return $target;
    }

    public static function dataSet(mixed &$target, string|array $key, mixed $value, bool $overwrite = true): mixed
    {
        if (is_null($key)) {
            return $target = $value;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        while (count($key) > 1) {
            $segment = array_shift($key);

            if (!isset($target[$segment]) || !is_array($target[$segment])) {
                $target[$segment] = [];
            }

            $target = &$target[$segment];
        }

        if ($overwrite || !array_key_exists($segment = array_shift($key), $target)) {
            $target[$segment] = $value;
        }

        return $target;
    }

    public static function dataExists(mixed $target, string|array|null $key): bool
    {
        if (is_null($key)) {
            return false;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        foreach ($key as $segment) {
            if (is_array($target) && array_key_exists($segment, $target)) {
                $target = $target[$segment];
            } elseif ($target instanceof \ArrayAccess && $target->offsetExists($segment)) {
                $target = $target[$segment];
            } else {
                return false;
            }
        }

        return true;
    }
}
