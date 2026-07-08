<?php

declare(strict_types=1);

namespace LoveGem\Scheduler;

use Closure;

class Event
{
    protected mixed $callback;

    protected string $name = '';

    protected string $expression = '* * * * *';

    protected array $constraints = [];

    protected array $withoutOverlapping = [];

    protected bool $onOneServer = false;

    protected array $environments = [];

    protected ?string $emailTo = null;

    protected ?string $emailOnSuccess = null;

    protected bool $appendOutput = false;

    protected bool $emailOutput = false;

    protected ?string $outputPath = null;

    protected int $exitCode = 0;

    protected bool $ensureOutputIsInCurrentDirectory = false;

    public function __construct(mixed $callback)
    {
        $this->callback = $callback;
    }

    public function name(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function cron(string $expression): static
    {
        $this->expression = $expression;
        return $this;
    }

    public function evenInMaintenanceMode(): static
    {
        $this->withoutOverlapping[] = 'maintenance';
        return $this;
    }

    public function when(Closure $callback): static
    {
        $this->constraints[] = $callback;
        return $this;
    }

    public function skip(Closure $callback): static
    {
        $this->constraints[] = function () use ($callback) {
            return !$callback();
        };
        return $this;
    }

    public function environments(array $environments): static
    {
        $this->environments = $environments;
        return $this;
    }

    public function onOneServer(): static
    {
        $this->onOneServer = true;
        return $this;
    }

    public function withoutOverlapping(int $minutes = 25): static
    {
        $this->withoutOverlapping[] = $minutes;
        return $this;
    }

    public function runInBackground(): static
    {
        $this->ensureOutputIsInCurrentDirectory = true;
        return $this;
    }

    public function appendOutputTo(string $path): static
    {
        $this->outputPath = $path;
        $this->appendOutput = true;
        return $this;
    }

    public function sendOutputTo(string $path): static
    {
        $this->outputPath = $path;
        $this->appendOutput = false;
        return $this;
    }

    public function emailOutputOnFailure(string $email): static
    {
        $this->emailTo = $email;
        $this->emailOutput = true;
        return $this;
    }

    public function emailWrittenOutputOnFailure(string $email): static
    {
        $this->emailOnSuccess = $email;
        return $this;
    }

    public function emailOutputOnSuccess(string $email): static
    {
        $this->emailOnSuccess = $email;
        return $this;
    }

    public function isDue(): bool
    {
        if (!empty($this->environments) && !in_array(getenv('APP_ENV'), $this->environments)) {
            return false;
        }

        foreach ($this->constraints as $constraint) {
            if (!$constraint()) {
                return false;
            }
        }

        return $this->expressionMatches();
    }

    protected function expressionMatches(): bool
    {
        return true;
    }

    public function run(): mixed
    {
        if (is_callable($this->callback)) {
            return call_user_func($this->callback);
        }

        return null;
    }

    public function getCallback(): mixed
    {
        return $this->callback;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getExpression(): string
    {
        return $this->expression;
    }
}
