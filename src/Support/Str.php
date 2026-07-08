<?php

declare(strict_types=1);

namespace LoveGem\Support;

use Illuminate\Support\Traits\Macroable;

class Str
{
    use Macroable;

    protected static array $snakeCache = [];
    protected static array $camelCache = [];
    protected static array $studlyCache = [];
    protected static array $kebabCache = [];
    protected static array $titleCache = [];
    protected static array $startCache = [];
    protected static array $asciiCache = [];

    public static function of(string $value): static
    {
        return new static($value);
    }

    public static function camel(string $value): string
    {
        if (isset(static::$camelCache[$value])) {
            return static::$camelCache[$value];
        }

        return static::$camelCache[$value] = lcfirst(static::studly($value));
    }

    public static function contains(string $haystack, string|array $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    public static function containsAll(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (!static::contains($haystack, $needle)) {
                return false;
            }
        }

        return true;
    }

    public static function endsWith(string $haystack, string|array $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ((string) $needle === '') {
                return false;
            }

            if (substr($haystack, -strlen($needle)) === (string) $needle) {
                return true;
            }
        }

        return false;
    }

    public static function finish(string $value, string $cap): string
    {
        return preg_replace('/(?:' . preg_quote($cap, '/') . ')+$/u', '', $value) . $cap;
    }

    public static function is(string $pattern, string $value): bool
    {
        $patterns = Arr::wrap($pattern);

        foreach ($patterns as $pattern) {
            if ($pattern === $value) {
                return true;
            }

            $pattern = preg_quote($pattern, '#');

            $pattern = str_replace(
                ['\*', '\?'],
                ['.*', '.'],
                $pattern
            );

            if (preg_match('#^'.$pattern.'$#u', $value) === 1) {
                return true;
            }
        }

        return false;
    }

    public static function isEmpty(string $value): bool
    {
        return trim($value) === '';
    }

    public static function kebab(string $value): string
    {
        if (isset(static::$kebabCache[$value])) {
            return static::$kebabCache[$value];
        }

        return static::$kebabCache[$value] = str_replace('_', '-', mb_strtolower(static::snake($value)));
    }

    public static function length(string $value, ?string $encoding = null): int
    {
        if ($encoding === null) {
            return mb_strlen($value);
        }

        return mb_strlen($value, $encoding);
    }

    public static function limit(string $value, int $limit = 100, string $end = '...'): string
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $limit, 'UTF-8')).$end;
    }

    public static function lower(string $value): string
    {
        return mb_strtolower($value, 'UTF-8');
    }

    public static function words(string $value, int $words = 100, string $end = '...'): string
    {
        if (trim($value) === '') {
            return '';
        }

        preg_match('/^\s*+(?:\S++\s*++){1,'.$words.'}/u', $value, $matches);

        if (!isset($matches[0]) || static::length($matches[0]) === static::length($value)) {
            return $value;
        }

        return rtrim($matches[0]).$end;
    }

    public static function random(int $length = 16): string
    {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            $bytes = random_bytes($size);
            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }

    public static function replace(string $search, string $replace, string $subject): string
    {
        return str_replace($search, $replace, $subject);
    }

    public static function replaceArray(string $search, array $replace, string $subject): string
    {
        $searches = is_array($search) ? $search : [$search];

        $segmentCount = count($searches);
        $replaceCount = count($replace);

        $result = $subject;

        foreach ($searches as $index => $search) {
            if (!isset($replace[$index])) {
                continue;
            }

            $result = str_replace($search, $replace[$index], $result);
        }

        return $result;
    }

    public static function snake(string $value, string $delimiter = '_'): string
    {
        $key = $value;

        if (isset(static::$snakeCache[$key][$delimiter])) {
            return static::$snakeCache[$key][$delimiter];
        }

        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', $value);
            $value = mb_strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value), 'UTF-8');
        }

        return static::$snakeCache[$key][$delimiter] = $value;
    }

    public static function start(string $value, string $prefix): string
    {
        $encoded = preg_quote($prefix, '/');

        return $prefix.preg_replace('/^(?:'.$encoded.')+/u', '', $value);
    }

    public static function title(string $value): string
    {
        $words = explode(' ', $value);

        return implode(' ', array_map(function ($word) {
            return mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8').mb_substr($word, 1, null, 'UTF-8');
        }, $words));
    }

    public static function studly(string $value): string
    {
        $key = $value;

        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }

        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return static::$studlyCache[$key] = str_replace(' ', '', $value);
    }

    public static function ucfirst(string $value): string
    {
        return static::upper(static::substr($value, 0, 1)).static::substr($value, 1);
    }

    public static function substr(string $string, int $start, ?int $length = null): string
    {
        return mb_substr($string, $start, $length, 'UTF-8');
    }

    public static function substrCount(string $haystack, string $needle, int $offset = 0, ?int $length = null): int
    {
        if (is_null($length)) {
            return mb_substr_count($haystack, $needle, 'UTF-8', $offset);
        }

        return mb_substr_count(mb_substr($haystack, $offset, $length, 'UTF-8'), $needle, 'UTF-8');
    }

    public static function upper(string $value): string
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    public static function titleCase(string $value): string
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    public static function randomAscii(int $length = 16): string
    {
        return static::random($length);
    }

    public static function uuid(): string
    {
        $uuid = new \Rhukster\Uuid\Uuid();

        return $uuid->toString();
    }

    public static function slug(string $title, string $separator = '-', string $language = 'en'): string
    {
        $title = $language ? mb_strtolower($title, 'UTF-8') : $title;

        $flip = $separator === '-' ? '_' : '-';

        $title = preg_replace('!['.preg_quote($flip).']+!u', $separator, $title);

        $title = preg_replace('![^'.preg_quote($separator).'\pL\d]+!u', $separator, $title);

        $title = trim($title, $separator);

        return preg_replace('!['.preg_quote($separator).']+!u', $separator, $title);
    }

    public static function after(string $search, string $subject): string
    {
        if ($search === '') {
            return $subject;
        }

        $result = strstr($subject, (string) $search);

        return $result === false ? $subject : substr($result, strlen($search));
    }

    public static function afterLast(string $search, string $subject): string
    {
        if ($search === '') {
            return $subject;
        }

        $pos = strrpos($subject, (string) $search);

        if ($pos === false) {
            return $subject;
        }

        return substr($subject, $pos + strlen($search));
    }

    public static function before(string $search, string $subject): string
    {
        $result = strstr($subject, (string) $search, true);

        return $result === false ? $subject : $result;
    }

    public static function beforeLast(string $search, string $subject): string
    {
        if ($search === '') {
            return $subject;
        }

        $pos = strrpos($subject, (string) $search);

        if ($pos === false) {
            return $subject;
        }

        return substr($subject, 0, $pos);
    }

    public static function between(string $subject, string $start, string $end): string
    {
        if ($start === '' || $end === '') {
            return $subject;
        }

        $start = preg_quote($start, '/');
        $end = preg_quote($end, '/');

        preg_match('/'.$start.'(.*?)'.$end.'/s', $subject, $matches);

        return $matches[1] ?? $subject;
    }

    public static function betweenFirst(string $subject, string $start, string $end): string
    {
        if ($start === '' || $end === '') {
            return $subject;
        }

        $start = preg_quote($start, '/');
        $end = preg_quote($end, '/');

        preg_match('/'.$start.'(.*?)'.$end.'/s', $subject, $matches);

        return $matches[1] ?? $subject;
    }

    public static function chopStart(string $subject, string $trimmers): string
    {
        return preg_replace('/^'.preg_quote($trimmers, '/').'+/u', '', $subject);
    }

    public static function chopEnd(string $subject, string $trimmers): string
    {
        return preg_replace('/'.preg_quote($trimmers, '/').'+$/u', '', $subject);
    }

    public static function isUuid(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value) === 1;
    }

    public static function classBasename(object|string $class): string
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }

    public static function classNamespace(object|string $class): string
    {
        $class = is_object($class) ? get_class($class) : $class;

        return str_replace('\\'.static::classBasename($class), '', $class);
    }

    public static function classesHaveMacro(string $class): bool
    {
        return method_exists($class, 'hasMacro');
    }

    public static function createClassesOnlyHasMacro(array $classes): array
    {
        return array_filter($classes, function ($class) {
            return static::classesHaveMacro($class);
        });
    }

    public static function arrayHasMacro(string $class): bool
    {
        return method_exists($class, 'hasMacro');
    }

    public static function createArrayOnlyHasMacro(array $classes): array
    {
        return array_filter($classes, function ($class) {
            return static::arrayHasMacro($class);
        });
    }

    public static function parseCallback(string $callback, ?string $default = null): array
    {
        return static::contains($callback, '@')
            ? explode('@', $callback, 2)
            : [$callback, $default];
    }
}
