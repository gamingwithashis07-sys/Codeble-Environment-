<?php

declare(strict_types=1);

namespace LoveGem\Queue;

use LoveGem\Core\Application;

class Queue
{
    protected Application $app;

    protected array $jobs = [];

    protected string $connection;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function connection(string $connection = null): static
    {
        $this->connection = $connection;
        return $this;
    }

    public function push(object $job, mixed $data = null, string $queue = null): mixed
    {
        $jobData = [
            'id' => uniqid(),
            'job' => serialize($job),
            'data' => $data,
            'queue' => $queue ?? 'default',
            'attempts' => 0,
            'created_at' => time(),
        ];

        $this->jobs[] = $jobData;

        return $jobData['id'];
    }

    public function later(int $delay, object $job, mixed $data = null, string $queue = null): mixed
    {
        $jobData = [
            'id' => uniqid(),
            'job' => serialize($job),
            'data' => $data,
            'queue' => $queue ?? 'default',
            'attempts' => 0,
            'delay' => $delay,
            'created_at' => time(),
            'available_at' => time() + $delay,
        ];

        $this->jobs[] = $jobData;

        return $jobData['id'];
    }

    public function on(string $queue): static
    {
        $this->currentQueue = $queue;
        return $this;
    }

    public function size(string $queue = null): int
    {
        return count(array_filter($this->jobs, function ($job) use ($queue) {
            return $queue === null || $job['queue'] === $queue;
        }));
    }

    public function getJobs(): array
    {
        return $this->jobs;
    }

    public function pop(string $queue = null): ?array
    {
        foreach ($this->jobs as $index => $job) {
            if ($queue === null || $job['queue'] === $queue) {
                unset($this->jobs[$index]);
                return $job;
            }
        }

        return null;
    }

    public function release(object $job, int $delay = 0): void
    {
        $this->later($delay, $job);
    }

    public function forget(string $id): void
    {
        foreach ($this->jobs as $index => $job) {
            if ($job['id'] === $id) {
                unset($this->jobs[$index]);
                break;
            }
        }
    }

    public function clear(string $queue = null): void
    {
        if ($queue === null) {
            $this->jobs = [];
        } else {
            $this->jobs = array_filter($this->jobs, function ($job) use ($queue) {
                return $job['queue'] !== $queue;
            });
        }
    }
}
