<?php

declare(strict_types=1);

namespace LoveGem\Scheduler;

use Closure;

class Schedule
{
    protected array $events = [];

    protected array $jobs = [];

    protected string $timezone = 'UTC';

    public function call(callable $callback): Event
    {
        $event = new Event($callback);
        $this->events[] = $event;
        return $event;
    }

    public function command(string $command, array $parameters = []): Event
    {
        return $this->call(function () use ($command, $parameters) {
            $this->runCommand($command, $parameters);
        })->name($command);
    }

    public function job(object $job, string $queue = null): Event
    {
        return $this->call(function () use ($job, $queue) {
            $this->dispatchJob($job, $queue);
        })->name(get_class($job));
    }

    public function exec(string $command): Event
    {
        return $this->call(function () use ($command) {
            exec($command, $output, $returnCode);
            return $returnCode;
        })->name($command);
    }

    public function script(string $scriptPath): Event
    {
        return $this->call(function () use ($scriptPath) {
            exec("php {$scriptPath}", $output, $returnCode);
            return $returnCode;
        })->name($scriptPath);
    }

    public function everyMinute(): Event
    {
        return $this->cron('* * * * *');
    }

    public function everyFiveMinutes(): Event
    {
        return $this->cron('*/5 * * * *');
    }

    public function everyTenMinutes(): Event
    {
        return $this->cron('*/10 * * * *');
    }

    public function everyFifteenMinutes(): Event
    {
        return $this->cron('*/15 * * * *');
    }

    public function everyThirtyMinutes(): Event
    {
        return $this->cron('*/30 * * * *');
    }

    public function hourly(): Event
    {
        return $this->cron('0 * * * *');
    }

    public function hourlyAt(int $offset): Event
    {
        return $this->cron("{$offset} * * * *");
    }

    public function daily(): Event
    {
        return $this->cron('0 0 * * *');
    }

    public function dailyAt(string $time): Event
    {
        $offset = substr($time, 0, 2);
        $minute = substr($time, 3, 2);
        return $this->cron("{$minute} {$offset} * * *");
    }

    public function twiceDaily(int $first = 1, int $second = 13): Event
    {
        return $this->cron("0 {$first},{$second} * * *");
    }

    public function weekly(): Event
    {
        return $this->cron('0 0 * * 0');
    }

    public function weeklyOn(int $day = 1, string $time = '0:00'): Event
    {
        [$hour, $minute] = explode(':', $time);
        return $this->cron("{$minute} {$hour} * * {$day}");
    }

    public function monthly(): Event
    {
        return $this->cron('0 0 1 * *');
    }

    public function monthlyOn(int $day = 1, string $time = '0:00'): Event
    {
        [$hour, $minute] = explode(':', $time);
        return $this->cron("{$minute} {$hour} {$day} * *");
    }

    public function quarterly(): Event
    {
        return $this->cron('0 0 1 */3 *');
    }

    public function yearly(): Event
    {
        return $this->cron('0 0 1 1 *');
    }

    public function cron(string $expression): Event
    {
        $event = end($this->events);
        if ($event) {
            $event->cron($expression);
        }
        return $event;
    }

    public function timezone(string $timezone): static
    {
        $this->timezone = $timezone;
        return $this;
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function isDue(): array
    {
        $due = [];
        foreach ($this->events as $event) {
            if ($event->isDue()) {
                $due[] = $event;
            }
        }
        return $due;
    }

    protected function runCommand(string $command, array $parameters): void
    {
        $fullCommand = "php artisan {$command} " . implode(' ', $parameters);
        exec($fullCommand);
    }

    protected function dispatchJob(object $job, string $queue): void
    {
        app('queue')->push($job, null, $queue);
    }
}
