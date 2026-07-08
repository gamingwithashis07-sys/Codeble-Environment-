<?php

declare(strict_types=1);

namespace LoveGem\Console;

use LoveGem\Core\Application;

class Artisan
{
    protected Application $app;

    protected array $commands = [];

    protected array $commandMap = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->registerDefaultCommands();
    }

    protected function registerDefaultCommands(): void
    {
        $this->registerCommand('list', ListCommand::class);
        $this->registerCommand('serve', ServeCommand::class);
        $this->registerCommand('migrate', MigrateCommand::class);
        $this->registerCommand('migrate:rollback', RollbackCommand::class);
        $this->registerCommand('migrate:fresh', FreshCommand::class);
        $this->registerCommand('migrate:status', StatusCommand::class);
        $this->registerCommand('db:seed', SeedCommand::class);
        $this->registerCommand('cache:clear', ClearCacheCommand::class);
        $this->registerCommand('config:clear', ClearConfigCommand::class);
        $this->registerCommand('route:clear', ClearRouteCommand::class);
        $this->registerCommand('view:clear', ClearViewCommand::class);
        $this->registerCommand('queue:work', QueueWorkCommand::class);
        $this->registerCommand('queue:restart', QueueRestartCommand::class);
        $this->registerCommand('key:generate', GenerateKeyCommand::class);
        $this->registerCommand('make:model', MakeModelCommand::class);
        $this->registerCommand('make:controller', MakeControllerCommand::class);
        $this->registerCommand('make:migration', MakeMigrationCommand::class);
        $this->registerCommand('make:seeder', MakeSeederCommand::class);
        $this->registerCommand('make:factory', MakeFactoryCommand::class);
        $this->registerCommand('make:middleware', MakeMiddlewareCommand::class);
        $this->registerCommand('make:mail', MakeMailCommand::class);
        $this->registerCommand('make:notification', MakeNotificationCommand::class);
        $this->registerCommand('make:queue', MakeQueueCommand::class);
        $this->registerCommand('make:event', MakeEventCommand::class);
        $this->registerCommand('make:listener', MakeListenerCommand::class);
        $this->registerCommand('make:policy', MakePolicyCommand::class);
        $this->registerCommand('make:provider', MakeProviderCommand::class);
    }

    public function registerCommand(string $signature, string $class): void
    {
        $this->commands[$signature] = $class;
        $this->commandMap[$signature] = $signature;
    }

    public function getCommands(): array
    {
        return $this->commands;
    }

    public function all(): array
    {
        return $this->commands;
    }

    public function call(string $command, array $parameters = []): int
    {
        $this->validateCommand($command);

        $commandClass = $this->commands[$command];

        $commandInstance = new $commandClass($this->app);

        return $commandInstance->handle($parameters);
    }

    public function output(): Output
    {
        return new Output();
    }

    protected function validateCommand(string $command): void
    {
        if (!isset($this->commands[$command])) {
            throw new \InvalidArgumentException("Command [{$command}] not defined.");
        }
    }
}
