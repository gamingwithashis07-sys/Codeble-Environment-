<?php

declare(strict_types=1);

namespace LoveGem\Console;

class ListCommand extends Command
{
    protected string $signature = 'list';

    protected string $description = 'List all commands';

    public function handle(array $parameters = []): int
    {
        $commands = $this->app->make(Artisan::class)->all();

        $this->info('Available commands:');
        $this->newLine();

        foreach ($commands as $signature => $class) {
            $instance = new $class($this->app);
            $this->line("  <info>{$signature}</info>  {$instance->getDescription()}");
        }

        return 0;
    }
}
