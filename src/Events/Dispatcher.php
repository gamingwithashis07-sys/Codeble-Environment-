<?php

declare(strict_types=1);

namespace LoveGem\Events;

use Closure;

class Dispatcher
{
    protected array $listeners = [];

    protected array $wildcards = [];

    protected array $fired = [];

    public function listen(string|array $events, mixed $listener = null): void
    {
        $this->shouldDiscoverEvents = false;

        foreach ((array) $events as $event) {
            if (is_null($listener)) {
                $this->wildcards[$event][] = $listener;
            } else {
                $this->listeners[$event][] = $this->makeListener($listener);
            }
        }
    }

    public function on(string $event, callable $listener): void
    {
        $this->listeners[$event][] = $listener;
    }

    public function push(string $event, callable $listener): void
    {
        $this->listeners[$event][] = $listener;
    }

    public function subscribe(object|string $subscriber): void
    {
        $subscriber = $this->resolveSubscriber($subscriber);
        $subscriber->subscribe($this);
    }

    protected function resolveSubscriber(object|string $subscriber): object
    {
        if (is_string($subscriber)) {
            return new $subscriber();
        }

        return $subscriber;
    }

    public function when(string|array $events, array|string $listeners): void
    {
        foreach ((array) $events as $event) {
            foreach ((array) $listeners as $listener) {
                $this->addSubscribes($event, $listener);
            }
        }
    }

    protected function addSubscribes(string $event, string $listener): void
    {
        $this->listeners[$event][] = $this->makeListener($listener);
    }

    public function until(string|object $event, mixed $payload = []): mixed
    {
        return $this->dispatch($event, $payload, true);
    }

    public function dispatch(string|object $event, mixed $payload = [], bool $halt = false): mixed
    {
        $event = $this->parseEventName($event);

        $this->fired[] = $event;

        $responses = [];

        foreach ($this->getListeners($event) as $listener) {
            $response = $listener($event, $payload);

            if ($halt && !is_null($response)) {
                return $response;
            }

            if ($response === false) {
                break;
            }

            $responses[] = $response;
        }

        return $halt ? ($responses ? end($responses) : null) : $responses;
    }

    protected function parseEventName(string|object $event): string
    {
        if (is_object($event)) {
            return get_class($event);
        }

        return $event;
    }

    public function getListeners(string $event): array
    {
        $listeners = $this->listeners[$event] ?? [];

        foreach ($this->wildcards as $pattern => $wildcardListeners) {
            if (str_is($pattern, $event)) {
                $listeners = array_merge($listeners, $wildcardListeners);
            }
        }

        return $listeners;
    }

    public function hasListeners(string $event): bool
    {
        return !empty($this->listeners[$event]) || !empty($this->wildcards[$event]);
    }

    public function forget(string $event): void
    {
        unset($this->listeners[$event]);
    }

    public function forgetPushed(): void
    {
        $this->wildcards = [];
    }

    public function removeListener(string $event, callable $listener): void
    {
        if (isset($this->listeners[$event])) {
            foreach ($this->listeners[$event] as $key => $registeredListener) {
                if ($this->listenerMatches($registeredListener, $listener)) {
                    unset($this->listeners[$event][$key]);
                }
            }
        }
    }

    protected function listenerMatches(mixed $registeredListener, callable $listener): bool
    {
        if ($registeredListener instanceof Closure && $listener instanceof Closure) {
            return $registeredListener === $listener;
        }

        return false;
    }

    public function shouldDiscoverEvents(): bool
    {
        return $this->shouldDiscoverEvents ?? false;
    }

    public function discoverEventsUsing(callable $callback): void
    {
        $this->eventDiscoveryCallback = $callback;
    }

    protected function makeListener(callable|string $listener): callable
    {
        if (is_string($listener)) {
            return function (string $event, array $payload) use ($listener) {
                return $this->createClassListener($listener)($event, $payload);
            };
        }

        return function (string $event, array $payload) use ($listener) {
            return $listener($event, $payload);
        };
    }

    protected function createClassListener(string $listener): callable
    {
        return function (string $event, array $payload) use ($listener) {
            if (str_contains($listener, '@')) {
                [$class, $method] = $this->parseClassCallable($listener);
                return $this->callClassListener($class, $method, $event, $payload);
            }

            return $this->callClassListener($listener, 'handle', $event, $payload);
        };
    }

    protected function callClassListener(string $class, string $method, string $event, array $payload): mixed
    {
        if (method_exists($class, 'setEventDispatcher')) {
            $class::setEventDispatcher($this);
        }

        $listener = $this->createClassListener($class);

        return $listener($event, $payload);
    }

    protected function parseClassCallable(string $listener): array
    {
        return Str::parseCallback($listener, 'handle');
    }

    public function getFired(): array
    {
        return $this->fired;
    }

    public function __call(string $method, array $parameters): mixed
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        if (static::hasGlobalMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        throw new \BadMethodCallException("Method [{$method}] does not exist on the event dispatcher.");
    }

    protected function macroCall(string $method, array $parameters): mixed
    {
        $macro = static::getMacro($method);

        if ($macro instanceof Closure) {
            return $macro(...$parameters);
        }

        return $macro(...$parameters);
    }
}
