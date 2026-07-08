<?php

declare(strict_types=1);

namespace LoveGem\Health;

class HealthChecker
{
    protected array $checks = [];

    protected array $results = [];

    public function check(string $name, callable $callback): void
    {
        $this->checks[$name] = $callback;
    }

    public function run(): array
    {
        foreach ($this->checks as $name => $callback) {
            try {
                $result = $callback();

                $this->results[$name] = [
                    'status' => $result ? 'ok' : 'failed',
                    'message' => $result ? 'Check passed' : 'Check failed',
                ];
            } catch (\Throwable $e) {
                $this->results[$name] = [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $this->results;
    }

    public function getStatus(): string
    {
        $results = $this->run();

        foreach ($results as $result) {
            if ($result['status'] !== 'ok') {
                return 'failed';
            }
        }

        return 'ok';
    }

    public function isHealthy(): bool
    {
        return $this->getStatus() === 'ok';
    }

    public function report(): array
    {
        return [
            'status' => $this->getStatus(),
            'checks' => $this->run(),
            'timestamp' => date('c'),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->report());
    }

    public function registerDefaultChecks(): void
    {
        $this->check('database', function () {
            try {
                app('db')->getPdo();
                return true;
            } catch (\Throwable $e) {
                return false;
            }
        });

        $this->check('cache', function () {
            try {
                app('cache')->put('health_check', true, 10);
                return app('cache')->get('health_check') === true;
            } catch (\Throwable $e) {
                return false;
            }
        });

        $this->check('session', function () {
            try {
                app('session')->start();
                return true;
            } catch (\Throwable $e) {
                return false;
            }
        });

        $this->check('storage', function () {
            return is_writable(storage_path());
        });

        $this->check('config', function () {
            return app('config')->get('app.name') !== null;
        });

        $this->check('queue', function () {
            try {
                return app('queue') !== null;
            } catch (\Throwable $e) {
                return false;
            }
        });
    }
}
