<?php

use LoveGem\Support\Str;
use LoveGem\Support\Arr;

if (!function_exists('data_get')) {
    function data_get(mixed $target, string|array|null $key, mixed $default = null): mixed
    {
        return Arr::dataGet($target, $key, $default);
    }
}

if (!function_exists('data_set')) {
    function data_set(mixed &$target, string|array $key, mixed $value, bool $overwrite = true): mixed
    {
        return Arr::dataSet($target, $key, $value, $overwrite);
    }
}

if (!function_exists('data_has')) {
    function data_has(mixed $target, string|array|null $key): bool
    {
        return Arr::dataExists($target, $key);
    }
}

if (!function_exists('collect')) {
    function collect(mixed $value = null): LoveGem\Database\Eloquent\Collection
    {
        return new LoveGem\Database\Eloquent\Collection((array) $value);
    }
}

if (!function_exists('config')) {
    function config(?string $key = null, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return app('config');
        }

        return data_get(app('config')->all(), $key, $default);
    }
}

if (!function_exists('app')) {
    function app(?string $abstract = null, array $parameters = []): mixed
    {
        if (is_null($abstract)) {
            return LoveGem\Container\Container::getInstance();
        }

        return LoveGem\Container\Container::getInstance()->make($abstract, $parameters);
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return app('path').($path ? DIRECTORY_SEPARATOR.$path : '');
    }
}

if (!function_exists('resource_path')) {
    function resource_path(string $path = ''): string
    {
        return app('path.resources').($path ? DIRECTORY_SEPARATOR.$path : '');
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return app('path.storage').($path ? DIRECTORY_SEPARATOR.$path : '');
    }
}

if (!function_exists('database_path')) {
    function database_path(string $path = ''): string
    {
        return app('path.database').($path ? DIRECTORY_SEPARATOR.$path : '');
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        return app('path.public').($path ? DIRECTORY_SEPARATOR.$path : '');
    }
}

if (!function_exists('config_path')) {
    function config_path(string $path = ''): string
    {
        return app('path.config').($path ? DIRECTORY_SEPARATOR.$path : '');
    }
}

if (!function_exists('now')) {
    function now(): Carbon\Carbon
    {
        return new Carbon\Carbon();
    }
}

if (!function_exists('today')) {
    function today(): Carbon\Carbon
    {
        return (new Carbon\Carbon())->startOfDay();
    }
}

if (!function_exists('tomorrow')) {
    function tomorrow(): Carbon\Carbon
    {
        return (new Carbon\Carbon())->addDay()->startOfDay();
    }
}

if (!function_exists('yesterday')) {
    function yesterday(): Carbon\Carbon
    {
        return (new Carbon\Carbon())->subDay()->startOfDay();
    }
}

if (!function_exists('str')) {
    function str(string $string): LoveGem\Support\Stringable
    {
        return new LoveGem\Support\Stringable($string);
    }
}

if (!function_exists('Str')) {
    function Str(): LoveGem\Support\Str
    {
        return new LoveGem\Support\Str();
    }
}

if (!function_exists('class_basename')) {
    function class_basename(object|string $class): string
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}

if (!function_exists('class_uses_recursive')) {
    function class_uses_recursive(object|string $class): array
    {
        $results = [];

        foreach (array_reverse(class_parents($class)) as $class) {
            $results += trait_uses_recursive($class);
        }

        return $results + trait_uses_recursive($class);
    }
}

if (!function_exists('trait_uses_recursive')) {
    function trait_uses_recursive(string $trait): array
    {
        $traits = class_uses($trait);

        foreach ($traits as $trait) {
            $traits += trait_uses_recursive($trait);
        }

        return $traits;
    }
}

if (!function_exists('value')) {
    function value(mixed $value, mixed ...$args): mixed
    {
        return $value instanceof Closure ? $value(...$args) : $value;
    }
}

if (!function_exists('tap')) {
    function tap(mixed $value, ?callable $callback = null): mixed
    {
        if (is_null($callback)) {
            return $value;
        }

        $callback($value);

        return $value;
    }
}

if (!function_exists('object_get')) {
    function object_get(object $object, string $key, mixed $default = null): mixed
    {
        if (is_null($key) || trim($key) === '') {
            return $default;
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_object($object) || !property_exists($object, $segment)) {
                return $default;
            }

            $object = $object->{$segment};
        }

        return $object;
    }
}

if (!function_exists('head')) {
    function head(array $array): mixed
    {
        return reset($array);
    }
}

if (!function_exists('last')) {
    function last(array $array): mixed
    {
        return end($array);
    }
}

if (!function_exists('array_first')) {
    function array_first(array $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if (is_null($callback)) {
            return reset($array);
        }

        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return $default;
    }
}

if (!function_exists('array_last')) {
    function array_last(array $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if (is_null($callback)) {
            return end($array);
        }

        return array_first(array_reverse($array), $callback, $default);
    }
}

if (!function_exists('array_flatten')) {
    function array_flatten(array $array, int $depth = INF): array
    {
        $result = [];

        foreach ($array as $item) {
            if (!is_array($item)) {
                $result[] = $item;
            } elseif ($depth === 1) {
                $result = array_merge($result, array_values($item));
            } else {
                $result = array_merge($result, array_flatten($item, $depth - 1));
            }
        }

        return $result;
    }
}

if (!function_exists('array_except')) {
    function array_except(array $array, array|string $keys): array
    {
        return Arr::except($array, $keys);
    }
}

if (!function_exists('array_only')) {
    function array_only(array $array, array|string $keys): array
    {
        return Arr::only($array, $keys);
    }
}

if (!function_exists('array_get')) {
    function array_get(array $array, string $key, mixed $default = null): mixed
    {
        return Arr::get($array, $key, $default);
    }
}

if (!function_exists('array_set')) {
    function array_set(array &$array, string $key, mixed $value): array
    {
        return Arr::set($array, $key, $value);
    }
}

if (!function_exists('array_has')) {
    function array_has(array $array, string|array $keys): bool
    {
        return Arr::has($array, $keys);
    }
}

if (!function_exists('array_pluck')) {
    function array_pluck(array $array, string|array $value, ?string $key = null): array
    {
        return Arr::pluck($array, $value, $key);
    }
}

if (!function_exists('array_sort')) {
    function array_sort(array $array, callable $callback): array
    {
        return Arr::sort($array, $callback);
    }
}

if (!function_exists('array_sort_recursive')) {
    function array_sort_recursive(array $array): array
    {
        return Arr::sortRecursive($array);
    }
}

if (!function_exists('array_where')) {
    function array_where(array $array, callable $callback): array
    {
        return Arr::where($array, $callback);
    }
}

if (!function_exists('array_wrap')) {
    function array_wrap(mixed $value): array
    {
        return Arr::wrap($value);
    }
}

if (!function_exists('array_add')) {
    function array_add(array $array, string $key, mixed $value): array
    {
        return Arr::add($array, $key, $value);
    }
}

if (!function_exists('array_collapse')) {
    function array_collapse(array $arrays): array
    {
        return Arr::collapse($arrays);
    }
}

if (!function_exists('array_divide')) {
    function array_divide(array $array): array
    {
        return Arr::divide($array);
    }
}

if (!function_exists('array_dot')) {
    function array_dot(array $array, string $prepend = ''): array
    {
        return Arr::dot($array, $prepend);
    }
}

if (!function_exists('array_pull')) {
    function array_pull(array &$array, string $key, mixed $default = null): mixed
    {
        return Arr::pull($array, $key, $default);
    }
}

if (!function_exists('array_forget')) {
    function array_forget(array &$array, string|array $keys): void
    {
        Arr::forget($array, $keys);
    }
}

if (!function_exists('array_first')) {
    function array_first(array $array, callable $callback, mixed $default = null): mixed
    {
        return Arr::first($array, $callback, $default);
    }
}

if (!function_exists('array_last')) {
    function array_last(array $array, callable $callback, mixed $default = null): mixed
    {
        return Arr::last($array, $callback, $default);
    }
}

if (!function_exists('array_prepend')) {
    function array_prepend(array $array, mixed $value, string $key = null): array
    {
        return Arr::prepend($array, $value, $key);
    }
}

if (!function_exists('array_push')) {
    function array_push(array &$array, mixed $value): int
    {
        return array_push($array, $value);
    }
}

if (!function_exists('array_pull')) {
    function array_pull(array &$array, string $key, mixed $default = null): mixed
    {
        return Arr::pull($array, $key, $default);
    }
}

if (!function_exists('array_random')) {
    function array_random(array $array, int $number = 1): mixed
    {
        $keys = array_rand($array, $number);

        if ($number === 1) {
            return $array[$keys];
        }

        return array_map(fn ($key) => $array[$key], $keys);
    }
}

if (!function_exists('array_set')) {
    function array_set(array &$array, string $key, mixed $value): bool
    {
        return Arr::set($array, $key, $value);
    }
}

if (!function_exists('array_sort')) {
    function array_sort(array $array, callable $callback = null): array
    {
        return Arr::sort($array, $callback);
    }
}

if (!function_exists('array_sort_recursive')) {
    function array_sort_recursive(array $array): array
    {
        return Arr::sortRecursive($array);
    }
}

if (!function_exists('array_where')) {
    function array_where(array $array, callable $callback): array
    {
        return Arr::where($array, $callback);
    }
}

if (!function_exists('array_wrap')) {
    function array_wrap(mixed $value): array
    {
        return Arr::wrap($value);
    }
}

if (!function_exists('array_zip')) {
    function array_zip(array ...$arrays): array
    {
        return array_map(function (...$values) {
            return $values;
        }, ...$arrays);
    }
}

if (!function_exists('blank')) {
    function blank(mixed $value): bool
    {
        if (is_string($value)) {
            return trim($value) === '';
        }

        return empty($value);
    }
}

if (!function_exists('class_basename')) {
    function class_basename(object|string $class): string
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}

if (!function_exists('class_uses_recursive')) {
    function class_uses_recursive(object|string $class): array
    {
        $results = [];

        foreach (array_reverse(class_parents($class)) as $class) {
            $results += trait_uses_recursive($class);
        }

        return $results + trait_uses_recursive($class);
    }
}

if (!function_exists('trait_uses_recursive')) {
    function trait_uses_recursive(string $trait): array
    {
        $traits = class_uses($trait);

        foreach ($traits as $trait) {
            $traits += trait_uses_recursive($trait);
        }

        return $traits;
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        if (is_null($value)) {
            return '';
        }

        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('filled')) {
    function filled(mixed $value): bool
    {
        return !blank($value);
    }
}

if (!function_exists('object_get')) {
    function object_get(object $object, string $key, mixed $default = null): mixed
    {
        if (is_null($key) || trim($key) === '') {
            return $default;
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_object($object) || !property_exists($object, $segment)) {
                return $default;
            }

            $object = $object->{$segment};
        }

        return $object;
    }
}

if (!function_exists('optional')) {
    function optional(?object $value, ?callable $callback = null): mixed
    {
        if (is_null($callback)) {
            return $value;
        }

        return $value ? $callback($value) : null;
    }
}

if (!function_exists('retry')) {
    function retry(int $times, callable $callback, int $sleep = 0): mixed
    {
        $attempts = 0;

        beginning:
        try {
            return $callback();
        } catch (\Throwable $e) {
            $attempts++;

            if ($attempts >= $times) {
                throw $e;
            }

            if ($sleep) {
                usleep($sleep * 1000);
            }

            goto beginning;
        }
    }
}

if (!function_exists('transform')) {
    function transform(mixed $value, callable $callback, mixed $default = null): mixed
    {
        return $value ? $callback($value) : $default;
    }
}

if (!function_exists('validator')) {
    function validator(array $data, array $rules, array $messages = [], array $customAttributes = []): \LoveGem\Validation\Factory
    {
        $factory = app('validator');

        return $factory->make($data, $rules, $messages, $customAttributes);
    }
}

if (!function_exists('bcrypt')) {
    function bcrypt(string $value, int $rounds = 12): string
    {
        return password_hash($value, PASSWORD_BCRYPT, ['cost' => $rounds]);
    }
}

if (!function_exists('value')) {
    function value(mixed $value, mixed ...$args): mixed
    {
        return $value instanceof Closure ? $value(...$args) : $value;
    }
}

if (!function_exists('str_after')) {
    function str_after(string $subject, string $search): string
    {
        return Str::after($subject, $search);
    }
}

if (!function_exists('str_before')) {
    function str_before(string $subject, string $search): string
    {
        return Str::before($subject, $search);
    }
}

if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needles): bool
    {
        return Str::contains($haystack, $needles);
    }
}

if (!function_exists('str_finish')) {
    function str_finish(string $value, string $cap): string
    {
        return Str::finish($value, $cap);
    }
}

if (!function_exists('str_is')) {
    function str_is(string $pattern, string $value): bool
    {
        return Str::is($pattern, $value);
    }
}

if (!function_exists('str_limit')) {
    function str_limit(string $value, int $limit = 100, string $end = '...'): string
    {
        return Str::limit($value, $limit, $end);
    }
}

if (!function_exists('str_plural')) {
    function str_plural(string $value, int $count = 2): string
    {
        return Str::plural($value, $count);
    }
}

if (!function_exists('str_random')) {
    function str_random(int $length = 16): string
    {
        return Str::random($length);
    }
}

if (!function_exists('str_replace_first')) {
    function str_replace_first(string $search, string $replace, string $subject): string
    {
        return Str::replaceFirst($search, $replace, $subject);
    }
}

if (!function_exists('str_replace_last')) {
    function str_replace_last(string $search, string $replace, string $subject): string
    {
        return Str::replaceLast($search, $replace, $subject);
    }
}

if (!function_exists('str_slug')) {
    function str_slug(string $title, string $separator = '-', string $language = 'en'): string
    {
        return Str::slug($title, $separator, $language);
    }
}

if (!function_exists('str_start')) {
    function str_start(string $value, string $prefix): string
    {
        return Str::start($value, $prefix);
    }
}

if (!function_exists('title_case')) {
    function title_case(string $value): string
    {
        return Str::title($value);
    }
}

if (!function_exists('snake_case')) {
    function snake_case(string $value, string $delimiter = '_'): string
    {
        return Str::snake($value, $delimiter);
    }
}

if (!function_exists('camel_case')) {
    function camel_case(string $value): string
    {
        return Str::camel($value);
    }
}

if (!function_exists('studly_case')) {
    function studly_case(string $value): string
    {
        return Str::studly($value);
    }
}

if (!function_exists('kebab_case')) {
    function kebab_case(string $value): string
    {
        return Str::kebab($value);
    }
}

if (!function_exists('str_random')) {
    function str_random(int $length = 16): string
    {
        return Str::random($length);
    }
}

if (!function_exists('str_replace_first')) {
    function str_replace_first(string $search, string $replace, string $subject): string
    {
        return Str::replaceFirst($search, $replace, $subject);
    }
}

if (!function_exists('str_replace_last')) {
    function str_replace_last(string $search, string $replace, string $subject): string
    {
        return Str::replaceLast($search, $replace, $subject);
    }
}

if (!function_exists('str_slug')) {
    function str_slug(string $title, string $separator = '-', string $language = 'en'): string
    {
        return Str::slug($title, $separator, $language);
    }
}

if (!function_exists('str_start')) {
    function str_start(string $value, string $prefix): string
    {
        return Str::start($value, $prefix);
    }
}

if (!function_exists('title_case')) {
    function title_case(string $value): string
    {
        return Str::title($value);
    }
}

if (!function_exists('snake_case')) {
    function snake_case(string $value, string $delimiter = '_'): string
    {
        return Str::snake($value, $delimiter);
    }
}

if (!function_exists('camel_case')) {
    function camel_case(string $value): string
    {
        return Str::camel($value);
    }
}

if (!function_exists('studly_case')) {
    function studly_case(string $value): string
    {
        return Str::studly($value);
    }
}

if (!function_exists('kebab_case')) {
    function kebab_case(string $value): string
    {
        return Str::kebab($value);
    }
}

if (!function_exists('str_random')) {
    function str_random(int $length = 16): string
    {
        return Str::random($length);
    }
}

if (!function_exists('str_replace_first')) {
    function str_replace_first(string $search, string $replace, string $subject): string
    {
        return Str::replaceFirst($search, $replace, $subject);
    }
}

if (!function_exists('str_replace_last')) {
    function str_replace_last(string $search, string $replace, string $subject): string
    {
        return Str::replaceLast($search, $replace, $subject);
    }
}

if (!function_exists('str_slug')) {
    function str_slug(string $title, string $separator = '-', string $language = 'en'): string
    {
        return Str::slug($title, $separator, $language);
    }
}

if (!function_exists('str_start')) {
    function str_start(string $value, string $prefix): string
    {
        return Str::start($value, $prefix);
    }
}

if (!function_exists('title_case')) {
    function title_case(string $value): string
    {
        return Str::title($value);
    }
}

if (!function_exists('snake_case')) {
    function snake_case(string $value, string $delimiter = '_'): string
    {
        return Str::snake($value, $delimiter);
    }
}

if (!function_exists('camel_case')) {
    function camel_case(string $value): string
    {
        return Str::camel($value);
    }
}

if (!function_exists('studly_case')) {
    function studly_case(string $value): string
    {
        return Str::studly($value);
    }
}

if (!function_exists('kebab_case')) {
    function kebab_case(string $value): string
    {
        return Str::kebab($value);
    }
}
