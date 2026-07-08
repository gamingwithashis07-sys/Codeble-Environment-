<?php

declare(strict_types=1);

namespace LoveGem\Support;

class Stringable implements \JsonSerializable, \Stringable
{
    protected string $value;

    public function __construct(string $value = '')
    {
        $this->value = $value;
    }

    public static function of(string $value): static
    {
        return new static($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function append(string ...$values): static
    {
        return new static($this->value . implode('', $values));
    }

    public function prepend(string ...$values): static
    {
        return new static(implode('', $values) . $this->value);
    }

    public function after(string $search): static
    {
        return new static(Str::after($this->value, $search));
    }

    public function afterLast(string $search): static
    {
        return new static(Str::afterLast($this->value, $search));
    }

    public function before(string $search): static
    {
        return new static(Str::before($this->value, $search));
    }

    public function beforeLast(string $search): static
    {
        return new static(Str::beforeLast($this->value, $search));
    }

    public function between(string $start, string $end): static
    {
        return new static(Str::between($this->value, $start, $end));
    }

    public function camel(): static
    {
        return new static(Str::camel($this->value));
    }

    public function contains(string|array $needles): bool
    {
        return Str::contains($this->value, $needles);
    }

    public function containsAll(array $needles): bool
    {
        return Str::containsAll($this->value, $needles);
    }

    public function endsWith(string|array $needles): bool
    {
        return Str::endsWith($this->value, $needles);
    }

    public function finish(string $cap): static
    {
        return new static(Str::finish($this->value, $cap));
    }

    public function is(string $pattern): bool
    {
        return Str::is($pattern, $this->value);
    }

    public function isEmpty(): bool
    {
        return $this->value === '';
    }

    public function kebab(): static
    {
        return new static(Str::kebab($this->value));
    }

    public function length(): int
    {
        return Str::length($this->value);
    }

    public function limit(int $limit = 100, string $end = '...'): static
    {
        return new static(Str::limit($this->value, $limit, $end));
    }

    public function lower(): static
    {
        return new static(Str::lower($this->value));
    }

    public function words(int $words = 100, string $end = '...'): static
    {
        return new static(Str::words($this->value, $words, $end));
    }

    public function replace(string $search, string $replace): static
    {
        return new static(Str::replace($search, $replace, $this->value));
    }

    public function replaceArray(string $search, array $replace): static
    {
        return new static(Str::replaceArray($search, $replace, $this->value));
    }

    public function snake(string $delimiter = '_'): static
    {
        return new static(Str::snake($this->value, $delimiter));
    }

    public function start(string $prefix): static
    {
        return new static(Str::start($this->value, $prefix));
    }

    public function title(): static
    {
        return new static(Str::title($this->value));
    }

    public function studly(): static
    {
        return new static(Str::studly($this->value));
    }

    public function substr(int $start, ?int $length = null): static
    {
        return new static(Str::substr($this->value, $start, $length));
    }

    public function ucfirst(): static
    {
        return new static(Str::ucfirst($this->value));
    }

    public function upper(): static
    {
        return new static(Str::upper($this->value));
    }

    public function slug(): static
    {
        return new static(Str::slug($this->value));
    }

    public function kebabCase(): static
    {
        return new static(Str::kebab($this->value));
    }

    public function snakeCase(): static
    {
        return new static(Str::snake($this->value));
    }

    public function camelCase(): static
    {
        return new static(Str::camel($this->value));
    }

    public function studlyCase(): static
    {
        return new static(Str::studly($this->value));
    }

    public function titleCase(): static
    {
        return new static(Str::title($this->value));
    }

    public function truncate(int $limit = 100, string $end = '...'): static
    {
        return new static(Str::limit($this->value, $limit, $end));
    }

    public function pad(int $length, string $padString = ' ', string $padType = STR_PAD_RIGHT): static
    {
        return new static(str_pad($this->value, $length, $padString, $padType));
    }

    public function padLeft(int $length, string $padString = ' '): static
    {
        return $this->pad($length, $padString, STR_PAD_LEFT);
    }

    public function padRight(int $length, string $padString = ' '): static
    {
        return $this->pad($length, $padString, STR_PAD_RIGHT);
    }

    public function padBoth(int $length, string $padString = ' '): static
    {
        return $this->pad($length, $padString, STR_PAD_BOTH);
    }

    public function split(int $length): array
    {
        return str_split($this->value, $length);
    }

    public function chunk(int $length): array
    {
        return array_map(function ($chunk) {
            return new static($chunk);
        }, str_split($this->value, $length));
    }

    public function wrap(int $limit): static
    {
        return new static(wordwrap($this->value, $limit, "\n"));
    }

    public function unwrap(): static
    {
        return new static(trim($this->value));
    }

    public function trim(string $characters = " \t\n\r\0\x0B"): static
    {
        return new static(trim($this->value, $characters));
    }

    public function ltrim(string $characters = " \t\n\r\0\x0B"): static
    {
        return new static(ltrim($this->value, $characters));
    }

    public function rtrim(string $characters = " \t\n\r\0\x0B"): static
    {
        return new static(rtrim($this->value, $characters));
    }

    public function reverse(): static
    {
        return new static(strrev($this->value));
    }

    public function repeat(int $times): static
    {
        return new static(str_repeat($this->value, $times));
    }

    public function replaceFirst(string $search, string $replace): static
    {
        $position = strpos($this->value, $search);

        if ($position !== false) {
            return new static(substr_replace($this->value, $replace, $position, strlen($search)));
        }

        return $this;
    }

    public function replaceLast(string $search, string $replace): static
    {
        $position = strrpos($this->value, $search);

        if ($position !== false) {
            return new static(substr_replace($this->value, $replace, $position, strlen($search)));
        }

        return $this;
    }

    public function replaceMatches(string $pattern, callable $callback): static
    {
        return new static(preg_replace_callback($pattern, $callback, $this->value));
    }

    public function explode(string $delimiter): array
    {
        return explode($delimiter, $this->value);
    }

    public function match(string $pattern): ?string
    {
        preg_match($pattern, $this->value, $matches);

        return $matches[1] ?? null;
    }

    public function matchAll(string $pattern): array
    {
        preg_match_all($pattern, $this->value, $matches);

        return $matches;
    }

    public function test(string $pattern): bool
    {
        return (bool) preg_match($pattern, $this->value);
    }

    public function isAlpha(): bool
    {
        return ctype_alpha($this->value);
    }

    public function isAlphaNumeric(): bool
    {
        return ctype_alnum($this->value);
    }

    public function isAscii(): bool
    {
        return mb_detect_encoding($this->value, 'ASCII', true) !== false;
    }

    public function isJson(): bool
    {
        json_decode($this->value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function isUrl(): bool
    {
        return filter_var($this->value, FILTER_VALIDATE_URL) !== false;
    }

    public function isUuid(): bool
    {
        return Str::isUuid($this->value);
    }

    public function isEmail(): bool
    {
        return filter_var($this->value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function isIp(): bool
    {
        return filter_var($this->value, FILTER_VALIDATE_IP) !== false;
    }

    public function isIpv4(): bool
    {
        return filter_var($this->value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    public function isIpv6(): bool
    {
        return filter_var($this->value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    public function isMacAddress(): bool
    {
        return filter_var($this->value, FILTER_VALIDATE_MAC) !== false;
    }

    public function isDigit(): bool
    {
        return ctype_digit($this->value);
    }

    public function isDecimal(): bool
    {
        return is_numeric($this->value) && str_contains($this->value, '.');
    }

    public function isNumeric(): bool
    {
        return is_numeric($this->value);
    }

    public function isFloat(): bool
    {
        return is_float((float) $this->value);
    }

    public function isInteger(): bool
    {
        return ctype_digit($this->value);
    }

    public function isBoolean(): bool
    {
        return in_array(strtolower($this->value), ['true', 'false', '1', '0', 'yes', 'no']);
    }

    public function isHex(): bool
    {
        return ctype_xdigit($this->value);
    }

    public function isBase64(): bool
    {
        return base64_encode(base64_decode($this->value)) === $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
