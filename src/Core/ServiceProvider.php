<?php

declare(strict_types=1);

namespace LoveGem\Core;

use Closure;

abstract class ServiceProvider
{
    protected Application $app;

    protected bool $defer = false;

    protected array $mergeConfig = [];

    protected array $publishes = [];

    protected array $publishGroups = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    abstract public function register(): void;

    public function boot(): void
    {
        //
    }

    public function provides(): array
    {
        return [];
    }

    public function isDeferred(): bool
    {
        return $this->defer;
    }

    public function mergeConfigFrom(string $path, string $key): void
    {
        $this->app->afterResolving('config', function ($config) use ($path, $key) {
            $default = require $path;
            $existing = $config->get($key, []);
            $config->set($key, array_replace_recursive($default, $existing));
        });
    }

    public function publishes(array $paths, string|array|null $groups = null): void
    {
        $this->publishes = array_merge($this->publishes, $paths);

        if (!is_null($groups)) {
            foreach ((array) $groups as $group) {
                $this->publishGroups[$group][] = $paths;
            }
        }
    }

    public function commands(array $commands): void
    {
        $this->commands = $commands;
    }

    public function afterResolvingCallbacks(Application $app): array
    {
        return [];
    }
}
