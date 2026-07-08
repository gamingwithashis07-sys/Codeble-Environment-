<?php

declare(strict_types=1);

namespace LoveGem\Auth;

use LoveGem\Core\Application;
use LoveGem\Session\Store;
use LoveGem\Hashing\HashManager;

class GuardManager
{
    protected Application $app;

    protected array $guards = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function guard(string $driver = null): Guard
    {
        $driver = $driver ?? $this->getDefaultDriver();

        return $this->guards[$driver] ?? $this->resolve($driver);
    }

    protected function resolve(string $driver): Guard
    {
        $config = $this->app['config']->get("auth.guards.{$driver}");

        return match ($driver) {
            'session' => new SessionGuard($this->app, $this->app['session']),
            default => new SessionGuard($this->app, $this->app['session']),
        };
    }

    public function getDefaultDriver(): string
    {
        return $this->app['config']->get('auth.default', 'session');
    }

    public function shouldUse(string $driver): void
    {
        $this->default = $driver;
    }
}
