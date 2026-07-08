<?php

declare(strict_types=1);

namespace LoveGem\Http;

use LoveGem\Http\Routing\Route;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request extends SymfonyRequest
{
    protected ?Route $routeResolver = null;

    protected ?array $json = null;

    protected ?array $decodedInput = null;

    protected ?array $queryParameters = null;

    protected ?array $allInput = null;

    protected array $routeParameters = [];

    protected array $attributesBag = [];

    public static function capture(): static
    {
        $request = static::create(
            $_SERVER['REQUEST_URI'],
            $_SERVER['REQUEST_METHOD'],
            [],
            $_COOKIE,
            [],
            $_SERVER
        );

        if ($request->isSecure()) {
            $request->headers->set('X-Forwarded-Proto', 'https');
        }

        return $request;
    }

    public static function createFromBase(SymfonyRequest $request): static
    {
        $request = (new static)->duplicate(
            $request->query->all(),
            $request->request->all(),
            $request->attributes->all(),
            $request->cookies->all(),
            $request->files->all(),
            $request->server->all()
        );

        $request->headers = $request->headers;

        return $request;
    }

    public function setRouteResolver(?Route $route): void
    {
        $this->routeResolver = $route;
    }

    public function getRouteResolver(): ?Route
    {
        return $this->routeResolver;
    }

    public function route(string $key = null, mixed $default = null): mixed
    {
        $route = $this->getRoute();

        if (is_null($route)) {
            return $default;
        }

        if (is_null($key)) {
            return $route;
        }

        return $route->getParameter($key, $default);
    }

    public function getRoute(): ?Route
    {
        return $this->routeResolver;
    }

    public function json(?string $key = null, mixed $default = null): mixed
    {
        if (!isset($this->json)) {
            $this->json = json_decode($this->getContent(), true) ?? [];
        }

        if (is_null($key)) {
            return $this->json;
        }

        return data_get($this->json, $key, $default);
    }

    public function input(string $key = null, mixed $default = null): mixed
    {
        return data_get($this->getInputSource()->all(), $key, $default);
    }

    public function all(?string $key = null): mixed
    {
        $input = array_merge($this->input(), $this->query->all());

        if (is_null($key)) {
            return $input;
        }

        return data_get($input, $key);
    }

    public function only(array $keys): array
    {
        $input = $this->getInputSource()->all();

        if (!is_array($keys)) {
            $keys = func_get_args();
        }

        $results = [];

        foreach ($keys as $key) {
            $results[$key] = data_get($input, $key);
        }

        return $results;
    }

    public function except(array $keys): array
    {
        $input = $this->getInputSource()->all();

        if (!is_array($keys)) {
            $keys = func_get_args();
        }

        return collect($input)->except($keys)->all();
    }

    public function has(string|array $keys): bool
    {
        $input = $this->getInputSource()->all();

        foreach ((array) $keys as $key) {
            if (!data_has($input, $key)) {
                return false;
            }
        }

        return true;
    }

    public function missing(string|array $keys): bool
    {
        return !$this->has($keys);
    }

    protected function getInputSource(): InputBag
    {
        return $this->isJson() ? $this->json() : $this->request;
    }

    public function isJson(): bool
    {
        return str_contains(
            $this->header('CONTENT_TYPE', ''),
            '/json'
        );
    }

    public function wantsJson(): bool
    {
        $acceptable = $this->getAcceptableContentTypes();

        return isset($acceptable[0]) && $acceptable[0] === 'application/json';
    }

    public function expectsJson(): bool
    {
        return $this->wantsJson() ||
               $this->isJson() ||
               $this->prefersJson();
    }

    public function prefersJson(): bool
    {
        return $this->bearerToken() !== null ||
               $this->ajax();
    }

    public function bearerToken(): ?string
    {
        return $this->bearerToken ??= $this->parseBearerToken();
    }

    protected function parseBearerToken(): ?string
    {
        $token = $this->bearerToken;

        if (!is_null($token)) {
            return $token;
        }

        $bearerToken = $this->header('Authorization');

        if (is_null($bearerToken)) {
            return null;
        }

        return str_starts_with($bearerToken, 'Bearer ') ? substr($bearerToken, 7) : null;
    }

    public function ajax(): bool
    {
        return $this->isXmlHttpRequest();
    }

    public function isXmlHttpRequest(): bool
    {
        return $this->headers->get('X-Requested-With') === 'XMLHttpRequest';
    }

    public function ip(): string
    {
        return $this->getClientIp() ?? '0.0.0.0';
    }

    public function ips(): array
    {
        $ip = $this->getClientIp();

        if (!$ip) {
            return [];
        }

        return explode(',', $ip);
    }

    public function userAgent(): ?string
    {
        return $this->headers->get('User-Agent');
    }

    public function path(): string
    {
        return parse_url($this->getRequestUri(), PHP_URL_PATH) ?: '/';
    }

    public function segments(): array
    {
        $segments = explode('/', trim($this->path(), '/'));

        return array_values(array_filter($segments));
    }

    public function segment(int $index, mixed $default = null): mixed
    {
        return $this->segments()[$index - 1] ?? $default;
    }

    public function is(string|array $patterns): bool
    {
        $path = $this->decodedPath();

        if (str_contains($path, '?')) {
            return false;
        }

        $patterns = (array) $patterns;

        foreach ($patterns as $pattern) {
            if (Str::is($pattern, $path)) {
                return true;
            }
        }

        return false;
    }

    public function routeIs(string|array $patterns): bool
    {
        $name = $this->route()?->getName();

        if (is_null($name)) {
            return false;
        }

        foreach ((array) $patterns as $pattern) {
            if (Str::is($pattern, $name)) {
                return true;
            }
        }

        return false;
    }

    public function fullUrl(): string
    {
        return $this->getUriForPath($this->decodedPath());
    }

    public function root(): string
    {
        return $this->getSchemeAndHttpHost();
    }

    public function refresh(): string
    {
        return $this->fullUrl();
    }

    public function __toString(): string
    {
        return $this->fullUrl();
    }
}
