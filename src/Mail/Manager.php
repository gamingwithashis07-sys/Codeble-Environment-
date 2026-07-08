<?php

declare(strict_types=1);

namespace LoveGem\Mail;

use LoveGem\Container\Container;

class Manager
{
    protected Application $app;

    protected array $drivers = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function mailer(string $driver = null): Mailer
    {
        $driver = $driver ?? $this->getDefaultDriver();

        return $this->drivers[$driver] ?? $this->resolve($driver);
    }

    protected function resolve(string $driver): Mailer
    {
        $config = $this->app['config']->get("mail.mailers.{$driver}");

        return $this->drivers[$driver] = new Mailer(
            $this->app,
            $this->app['view'],
            $this->app['dispatcher'] ?? null,
            $this->createTransport($driver)
        );
    }

    protected function createTransport(string $driver): Transport
    {
        return new Transport($driver);
    }

    public function getDefaultDriver(): string
    {
        return $this->app['config']->get('mail.default', 'smtp');
    }

    public function setDefaultDriver(string $driver): void
    {
        $this->app['config']->set('mail.default', $driver);
    }
}
