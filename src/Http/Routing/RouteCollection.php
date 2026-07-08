<?php

declare(strict_types=1);

namespace LoveGem\Http\Routing;

use LoveGem\Http\Request;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Serializable;

class RouteCollection implements Countable, IteratorAggregate, JsonSerializable, Serializable
{
    protected array $routes = [];

    protected array $allRoutes = [];

    protected array $actionList = [];

    protected array $nameList = [];

    protected array $actionListForRegex = [];

    protected array $nameListForRegex = [];

    protected array $addToStackCalled = [];

    protected array $originalList = [];

    protected array $sortedList = [];

    public function add(Route $route): void
    {
        $this->addToStackCalled[] = $route->getUri();

        $this->addRoute($route);
    }

    protected function addRoute(Route $route): void
    {
        $actions = $route->getAction();

        if (isset($actions['as'])) {
            $this->nameList[$actions['as']] = $route;
        }

        if (isset($actions['controller'])) {
            $this->actionList[$actions['controller']] = $route;
        }

        foreach ($route->getMethods() as $method) {
            $this->routes[$method][] = $route;
        }

        $this->allRoutes[$method.$route->getUri()] = $route;
    }

    public function refreshActionName(): void
    {
        foreach ($this->getRoutes() as $route) {
            $route->setAction(
                $this->parseAction($route->getAction())
            );
        }
    }

    public function match(Request $request): mixed
    {
        $routes = $this->getRoutesForUri($request->path());

        if (empty($routes)) {
            return null;
        }

        foreach ($routes as $route) {
            if ($route->matches($request->path(), $request->method())) {
                return $route;
            }
        }

        return null;
    }

    protected function getRoutesForUri(string $uri): array
    {
        $routes = [];

        foreach ($this->getRoutes() as $route) {
            if ($route->getUri() === $uri) {
                $routes[] = $route;
            }
        }

        return $routes;
    }

    public function getByName(string $name): ?Route
    {
        return $this->nameList[$name] ?? null;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function has(string $name): bool
    {
        return isset($this->nameList[$name]);
    }

    public function get(array|string $name, ?array $parameters = null): string
    {
        $route = $this->getByName($name);

        if (!$route) {
            throw new \InvalidArgumentException("Route [{$name}] not defined.");
        }

        return $this->toRoute($route, $parameters);
    }

    protected function toRoute(Route $route, ?array $parameters): string
    {
        $domain = $route->getDomain();

        $uri = $this->toUri($route->getUri(), $parameters);

        if (!empty($domain)) {
            $uri = $domain.$uri;
        }

        return $uri;
    }

    protected function toUri(string $uri, ?array $parameters): string
    {
        if (empty($parameters)) {
            return $uri;
        }

        $uri = str_replace(
            array_keys($parameters),
            array_values($parameters),
            $uri
        );

        return $uri;
    }

    public function current(): mixed
    {
        return $this->current ?? null;
    }

    public function setCurrentRoute(?Route $route): void
    {
        $this->current = $route;
    }

    public function remove(Route $route): void
    {
        $actions = $route->getAction();

        if (isset($actions['as'])) {
            unset($this->nameList[$actions['as']]);
        }

        if (isset($actions['controller'])) {
            unset($this->actionList[$actions['controller']]);
        }

        foreach ($route->getMethods() as $method) {
            $this->routes[$method] = array_filter(
                $this->routes[$method],
                fn ($r) => $r !== $route
            );
        }

        $this->refreshActionName();
    }

    public function matchAny(Request $request): ?Route
    {
        return $this->match($request);
    }

    public function count(): int
    {
        return count($this->getRoutes());
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->getRoutes());
    }

    public function toArray(): array
    {
        return $this->getRoutes();
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function serialize(): string
    {
        return serialize($this->toArray());
    }

    public function unserialize(string $data): void
    {
        $routes = unserialize($data);

        foreach ($routes as $route) {
            $this->add($route);
        }
    }

    public function getActionForController(string $controller): ?Route
    {
        return $this->actionList[$controller] ?? null;
    }

    public function getRoutesForController(string $controller): array
    {
        return array_filter(
            $this->getRoutes(),
            fn ($route) => $route->getController() === $controller
        );
    }

    public function __toString(): string
    {
        return (string) json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}
