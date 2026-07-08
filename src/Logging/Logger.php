<?php

declare(strict_types=1);

namespace LoveGem\Logging;

class Logger
{
    protected string $path;

    protected string $level = 'debug';

    protected array $levels = [
        'debug' => 0,
        'info' => 1,
        'notice' => 2,
        'warning' => 3,
        'error' => 4,
        'critical' => 5,
        'alert' => 6,
        'emergency' => 7,
    ];

    public function __construct(string $path, string $level = 'debug')
    {
        $this->path = $path;
        $this->level = $level;
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function notice(string $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    public function alert(string $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    public function emergency(string $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    public function log(string $level, string $message, array $context = []): void
    {
        if (!$this->shouldLog($level)) {
            return;
        }

        $message = $this->interpolate($message, $context);

        $logEntry = $this->formatLogEntry($level, $message, $context);

        $this->write($logEntry);
    }

    protected function shouldLog(string $level): bool
    {
        return ($this->levels[$level] ?? 0) >= ($this->levels[$this->level] ?? 0);
    }

    protected function interpolate(string $message, array $context): string
    {
        $replace = [];

        foreach ($context as $key => $val) {
            if (is_string($val) || (is_object($val) && method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = (string) $val;
            } elseif (is_array($val)) {
                $replace['{' . $key . '}'] = json_encode($val);
            }
        }

        return strtr($message, $replace);
    }

    protected function formatLogEntry(string $level, string $message, array $context): string
    {
        $timestamp = date('Y-m-d H:i:s');
        $level = strtoupper($level);

        $logEntry = "[{$timestamp}] {$level}: {$message}";

        if (!empty($context)) {
            $logEntry .= ' ' . json_encode($context);
        }

        return $logEntry . PHP_EOL;
    }

    protected function write(string $logEntry): void
    {
        $logFile = $this->path . '/' . date('Y-m-d') . '.log';

        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }

        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    public function setLevel(string $level): void
    {
        $this->level = $level;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function channel(string $channel): static
    {
        $clone = clone $this;
        $clone->path = $this->path . '/' . $channel;
        return $clone;
    }

    public function pushProcessor(callable $processor): static
    {
        $this->processors[] = $processor;
        return $this;
    }

    public function withContext(array $context): static
    {
        $clone = clone $this;
        $clone->context = array_merge($clone->context ?? [], $context);
        return $clone;
    }

    public function flush(): void
    {
        //
    }
}
