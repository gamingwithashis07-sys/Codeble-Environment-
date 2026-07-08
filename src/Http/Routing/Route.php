<?php

declare(strict_types=1);

namespace LoveGem\Http\Routing;

use Closure;
use LoveGem\Http\Request;
use LoveGem\Container\Container;

class Route
{
    protected array $methods;

    protected string $uri;

    protected array $action = [];

    protected array $parameters = [];

    protected ?Container $container;

    public function __construct(array|string $methods, string $uri, array $action)
    {
        $this->uri = trim($uri, '/') ?: '/';
        $this->methods = (array) $methods;
        $this->action = $action;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getAction(): array
    {
        return $this->action;
    }

    public function getName(): ?string
    {
        return $this->action['as'] ?? null;
    }

    public function getController(): ?string
    {
        return $this->action['controller'] ?? null;
    }

    public function getControllerMethod(): ?string
    {
        if (!isset($this->action['controller'])) {
            return null;
        }

        $parts = explode('@', $this->action['controller']);

        return $parts[1] ?? 'index';
    }

    public function getParameter(string $name, mixed $default = null): mixed
    {
        return $this->parameters[$name] ?? $default;
    }

    public function setParameter(string $name, mixed $value): void
    {
        $this->parameters[$name] = $value;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }

    public function middleware(array|string|null $middleware = null): static|Route
    {
        if (is_null($middleware)) {
            return $this;
        }

        $this->action['middleware'] = array_merge(
            $this->action['middleware'] ?? [],
            (array) $middleware
        );

        return $this;
    }

    public function getMiddleware(): array
    {
        return $this->action['middleware'] ?? [];
    }

    public function where(string|array $wheres): static
    {
        $this->action['wheres'] = array_merge(
            $this->action['wheres'] ?? [],
            is_array($wheres) ? $wheres : [$wheres]
        );

        return $this;
    }

    public function bind($request): void
    {
        if (isset($this->action['uses']) && is_callable($this->action['uses'])) {
            return;
        }

        $pattern = $this->buildPattern();

        if (preg_match($pattern, $request->path(), $matches)) {
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            foreach ($params as $key => $value) {
                $this->parameters[$key] = $value;
            }
        }
    }

    protected function buildPattern(): string
    {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $this->uri);
        return '#^' . $pattern . '$#';
    }

    public function matches(string $uri, string $method): bool
    {
        if (!in_array($method, $this->methods)) {
            return false;
        }

        return (bool) preg_match($this->buildPattern(), $uri);
    }

    public function run(): mixed
    {
        $this->container = $this->container ?? Container::getInstance();

        if (isset($this->action['uses']) && is_callable($this->action['uses'])) {
            return call_user_func_array($this->action['uses'], array_values($this->parameters));
        }

        if (isset($this->action['controller'])) {
            return $this->runController();
        }

        return null;
    }

    protected function runController(): mixed
    {
        $parts = explode('@', $this->action['controller']);
        $class = $parts[0];
        $method = $parts[1] ?? 'index';

        if (!class_exists($class)) {
            throw new \RuntimeException("Controller [{$class}] does not exist.");
        }

        $controller = $this->container->make($class);

        return $controller->{$method}(...array_values($this->parameters));
    }

    public function __toString(): string
    {
        return $this->uri;
    }
}
