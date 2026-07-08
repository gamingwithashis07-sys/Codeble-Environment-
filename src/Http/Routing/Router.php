<?php

declare(strict_types=1);

namespace LoveGem\Http\Routing;

use Closure;
use LoveGem\Http\Request;
use LoveGem\Http\Response;
use LoveGem\Container\Container;

class Router
{
    protected array $routes = [];

    protected array $groupStack = [];

    protected Container $container;

    protected array $middleware = [];

    protected array $middlewareGroups = [];

    protected array $middlewarePriority = [];

    protected ?string $currentRouteAction = null;

    protected ?string $currentControllerMiddleware = null;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function get(string $uri, array|callable|null $action = null): Route
    {
        return $this->addRoute(['GET', 'HEAD'], $uri, $action);
    }

    public function post(string $uri, array|callable|null $action = null): Route
    {
        return $this->addRoute('POST', $uri, $action);
    }

    public function put(string $uri, array|callable|null $action = null): Route
    {
        return $this->addRoute('PUT', $uri, $action);
    }

    public function patch(string $uri, array|callable|null $action = null): Route
    {
        return $this->addRoute('PATCH', $uri, $action);
    }

    public function delete(string $uri, array|callable|null $action = null): Route
    {
        return $this->addRoute('DELETE', $uri, $action);
    }

    public function options(string $uri, array|callable|null $action = null): Route
    {
        return $this->addRoute('OPTIONS', $uri, $action);
    }

    public function any(string $uri, array|callable|null $action = null): Route
    {
        return $this->addRoute(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE'], $uri, $action);
    }

    public function match(array|string $methods, string $uri, array|callable|null $action = null): Route
    {
        return $this->addRoute((array) $methods, $uri, $action);
    }

    protected function addRoute(array|string $methods, string $uri, array|callable|null $action): Route
    {
        $methods = (array) $methods;

        $action = $this->parseAction($action);

        foreach ($this->getPrefixes() as $prefix) {
            $uri = trim($prefix.'/'.$uri, '/') ?: '/';
        }

        $route = new Route($methods, $uri, $action);

        $route->bind($this->container);

        return $this->routes[] = $route;
    }

    protected function parseAction(array|callable|null $action): array
    {
        if (is_null($action)) {
            return [];
        }

        if (is_callable($action)) {
            return ['uses' => $action];
        }

        return $action;
    }

    protected function getPrefixes(): array
    {
        $prefixes = [];

        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefixes[] = $group['prefix'];
            }
        }

        return $prefixes;
    }

    public function group(array $attributes, Closure $callback): void
    {
        $this->updateGroupStack($attributes);

        $this->runMiddlewareStack($callback);

        array_pop($this->groupStack);
    }

    protected function updateGroupStack(array $attributes): void
    {
        if (!empty($this->groupStack)) {
            $attributes = $this->mergeWithLastGroup($attributes);
        }

        $this->groupStack[] = $attributes;
    }

    protected function mergeWithLastGroup(array $new): array
    {
        return array_merge_recursive(end($this->groupStack), $new);
    }

    public function prefix(string $prefix): PendingGroupMiddleware
    {
        return new PendingGroupMiddleware($this, $prefix);
    }

    protected function runMiddlewareStack(Closure $callback): void
    {
        $callback($this);
    }

    public function middleware(array|string $middleware): static
    {
        $this->middleware = array_merge($this->middleware, (array) $middleware);

        return $this;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function pushMiddlewareToGroup(string $group, string $middleware): void
    {
        if (!isset($this->middlewareGroups[$group])) {
            $this->middlewareGroups[$group] = [];
        }

        $this->middlewareGroups[$group][] = $middleware;
    }

    public function getMiddlewarePriority(): array
    {
        return $this->middlewarePriority;
    }

    public function getRouteCollection(): RouteCollection
    {
        return new RouteCollection($this->routes);
    }

    public function dispatch(Request $request): mixed
    {
        $route = $this->matchRoute($request);

        if (!$route) {
            return $this->getRoutes()->match($request);
        }

        return $this->runRoute($request, $route);
    }

    protected function matchRoute(Request $request): ?Route
    {
        $routes = $this->getRoutes();

        return $routes->match($request);
    }

    protected function runRoute(Request $request, Route $route): mixed
    {
        $request->setRouteResolver($route);

        $route->bind($request);

        return $route->run();
    }

    public function getCurrentRoute(): ?Route
    {
        return $this->currentRoute;
    }

    public function setCurrentRoute(?Route $route): void
    {
        $this->currentRoute = $route;
    }

    public function gatherMiddleware(?string $method = null, ?string $uri = null): array
    {
        $route = $this->getRoutes()->match(
            Request::create($uri, $method ?? 'GET')
        );

        return $route ? $route->gatherMiddleware() : [];
    }

    public function hasMiddlewareGroup(string $group): bool
    {
        return isset($this->middlewareGroups[$group]);
    }

    public function getMiddlewareGroups(): array
    {
        return $this->middlewareGroups;
    }

    public function resolveMiddlewareClassName(string $middleware): string
    {
        return $middleware;
    }

    public function substituteBindings(Route $route): Route
    {
        return $route;
    }

    public function toIlluminateRouteCollection(): RouteCollection
    {
        return new RouteCollection($this->routes);
    }
}
