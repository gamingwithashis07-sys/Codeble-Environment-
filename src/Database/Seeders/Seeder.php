<?php

declare(strict_types=1);

namespace LoveGem\Database\Seeders;

use LoveGem\Core\Application;

abstract class Seeder
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    abstract public function run(): void;

    protected function call(string $class): void
    {
        $seeder = new $class($this->app);
        $seeder->run();
    }

    protected function command(string $command): void
    {
        $this->app->make('artisan')->call($command);
    }
}
