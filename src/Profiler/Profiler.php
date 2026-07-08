<?php

declare(strict_types=1);

namespace LoveGem\Profiler;

class Profiler
{
    protected static ?Profiler $instance = null;

    protected array $startTimes = [];

    protected array $endTimes = [];

    protected array $queries = [];

    protected array $routes = [];

    protected array $exceptions = [];

    protected array $events = [];

    protected array $logs = [];

    protected float $startTime;

    public function __construct()
    {
        $this->startTime = microtime(true);
    }

    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function start(string $name): void
    {
        $this->startTimes[$name] = microtime(true);
    }

    public function stop(string $name): float
    {
        if (!isset($this->startTimes[$name])) {
            return 0;
        }

        $this->endTimes[$name] = microtime(true);

        return $this->elapsed($name);
    }

    public function elapsed(string $name): float
    {
        if (!isset($this->startTimes[$name]) || !isset($this->endTimes[$name])) {
            return 0;
        }

        return $this->endTimes[$name] - $this->startTimes[$name];
    }

    public function totalElapsed(): float
    {
        return microtime(true) - $this->startTime;
    }

    public function addQuery(array $query): void
    {
        $this->queries[] = array_merge($query, [
            'time' => microtime(true),
        ]);
    }

    public function getQueries(): array
    {
        return $this->queries;
    }

    public function getQueryCount(): int
    {
        return count($this->queries);
    }

    public function addRoute(array $route): void
    {
        $this->routes[] = array_merge($route, [
            'time' => microtime(true),
        ]);
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function addException(\Throwable $exception): void
    {
        $this->exceptions[] = [
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'time' => microtime(true),
        ];
    }

    public function getExceptions(): array
    {
        return $this->exceptions;
    }

    public function addEvent(string $event, mixed $data = null): void
    {
        $this->events[] = [
            'event' => $event,
            'data' => $data,
            'time' => microtime(true),
        ];
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function addLog(string $level, string $message, array $context = []): void
    {
        $this->logs[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'time' => microtime(true),
        ];
    }

    public function getLogs(): array
    {
        return $this->logs;
    }

    public function report(): array
    {
        return [
            'total_time' => $this->totalElapsed(),
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'queries' => $this->queries,
            'query_count' => $this->getQueryCount(),
            'routes' => $this->routes,
            'exceptions' => $this->exceptions,
            'events' => $this->events,
            'logs' => $this->logs,
            'timers' => $this->getTimers(),
        ];
    }

    protected function getTimers(): array
    {
        $timers = [];

        foreach ($this->startTimes as $name => $start) {
            $end = $this->endTimes[$name] ?? microtime(true);
            $timers[$name] = $end - $start;
        }

        return $timers;
    }

    public function toJson(): string
    {
        return json_encode($this->report());
    }

    public function render(): string
    {
        $report = $this->report();

        $html = '<div style="background:#f8f9fa;padding:20px;border:1px solid #ddd;margin:20px 0;font-family:monospace;">';
        $html .= '<h3>Profiler Report</h3>';
        $html .= '<p><strong>Total Time:</strong> ' . number_format($report['total_time'] * 1000, 2) . 'ms</p>';
        $html .= '<p><strong>Memory:</strong> ' . number_format($report['memory_usage'] / 1024 / 1024, 2) . 'MB</p>';
        $html .= '<p><strong>Queries:</strong> ' . $report['query_count'] . '</p>';
        $html .= '</div>';

        return $html;
    }
}
