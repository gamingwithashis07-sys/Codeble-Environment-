<?php

declare(strict_types=1);

namespace LoveGem\Console;

use LoveGem\Core\Application;

abstract class Command
{
    protected Application $app;

    protected Output $output;

    protected string $signature = '';

    protected string $description = '';

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->output = new Output();
    }

    abstract public function handle(array $parameters = []): int;

    public function getSignature(): string
    {
        return $this->signature;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    protected function info(string $message): void
    {
        $this->output->info($message);
    }

    protected function line(string $message): void
    {
        $this->output->line($message);
    }

    protected function error(string $message): void
    {
        $this->output->error($message);
    }

    protected function warn(string $message): void
    {
        $this->output->warn($message);
    }

    protected function ask(string $question, ?string $default = null): string
    {
        $this->output->write($question);

        if ($default !== null) {
            $this->output->write(" [{$default}]");
        }

        $this->output->write(': ');

        $input = trim(fgets(STDIN) ?: '');

        return $input !== '' ? $input : ($default ?? '');
    }

    protected function confirm(string $question, bool $default = true): bool
    {
        $suffix = $default ? '[Y/n]' : '[y/N]';
        $answer = $this->ask("{$question} {$suffix}");

        return $default ? $answer !== 'n' : $answer === 'y';
    }

    protected function secret(string $question): string
    {
        $this->output->write($question . ': ');
        $input = trim(fgets(STDIN) ?: '');

        return $input;
    }

    protected function table(array $headers, array $rows): void
    {
        $this->output->table($headers, $rows);
    }

    protected function newLine(int $count = 1): void
    {
        $this->output->newLine($count);
    }
}
