<?php

declare(strict_types=1);

namespace LoveGem\Console;

class ServeCommand extends Command
{
    protected string $signature = 'serve';

    protected string $description = 'Serve the application on the development server';

    public function handle(array $parameters = []): int
    {
        $host = $parameters['--host'] ?? '127.0.0.1';
        $port = $parameters['--port'] ?? '8000';

        $this->info("LoveGem development server started:");
        $this->info("http://{$host}:{$port}");

        $command = sprintf('php -S %s:%d -t %s', $host, $port, public_path());

        passthru($command);

        return 0;
    }
}
