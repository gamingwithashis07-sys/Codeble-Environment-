<?php

declare(strict_types=1);

namespace LoveGem\Console;

class MigrateCommand extends Command
{
    protected string $signature = 'migrate';

    protected string $description = 'Run the database migrations';

    public function handle(array $parameters = []): int
    {
        $this->info('Running migrations...');

        $path = database_path('migrations');

        if (!is_dir($path)) {
            $this->error('Migrations directory not found.');
            return 1;
        }

        $files = glob($path . '/*.php');

        if (empty($files)) {
            $this->info('No migrations found.');
            return 0;
        }

        sort($files);

        foreach ($files as $file) {
            $this->runMigration($file);
        }

        $this->info('Migrations complete!');
        return 0;
    }

    protected function runMigration(string $file): void
    {
        $class = $this->getMigrationClass($file);

        if (!class_exists($class)) {
            require_once $file;
        }

        $migration = new $class();
        $migration->up();

        $this->info("Migrated: " . basename($file));
    }

    protected function getMigrationClass(string $file): string
    {
        $name = basename($file, '.php');
        $parts = explode('_', $name);
        array_shift($parts);

        return implode('', array_map('ucfirst', $parts));
    }
}
