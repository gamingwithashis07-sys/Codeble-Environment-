<?php

declare(strict_types=1);

namespace LoveGem\RateLimiting;

use LoveGem\Cache\Repository;

class RateLimiter
{
    protected Repository $cache;

    protected array $maxAttempts = [];

    protected array $decayMinutes = [];

    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }

    public function limiter(string $key): RateLimiterBuilder
    {
        return new RateLimiterBuilder($this, $key);
    }

    public function attempt(string $key, int $maxAttempts, callable $callback, int $decayMinutes = 1): mixed
    {
        $key = $this->getKey($key);

        if ($this->tooManyAttempts($key, $maxAttempts)) {
            return $this->handleFailure($key, $maxAttempts, $decayMinutes);
        }

        $this->hit($key, $decayMinutes);

        return $callback();
    }

    public function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        return $this->cache->has($key.':lockout');
    }

    public function hit(string $key, int $decayMinutes = 1): int
    {
        $key = $this->getKey($key);

        $this->cache->put($key.':lockout', true, $decayMinutes * 60);

        $attempts = $this->cache->get($key, 0) + 1;
        $this->cache->put($key, $attempts, $decayMinutes * 60);

        return $attempts;
    }

    public function attempts(string $key): int
    {
        return $this->cache->get($this->getKey($key), 0);
    }

    public function remaining(string $key, int $maxAttempts): int
    {
        $attempts = $this->attempts($key);

        return $maxAttempts - $attempts;
    }

    public function clear(string $key): void
    {
        $this->cache->forget($this->getKey($key));
        $this->cache->forget($this->getKey($key).':lockout');
    }

    public function getRetryAfterDate(string $key): \DateTime
    {
        $decayMinutes = $this->decayMinutes[$key] ?? 1;

        return new \DateTime("+{$decayMinutes} minutes");
    }

    public function setMaxAttempts(string $key, int $maxAttempts): void
    {
        $this->maxAttempts[$key] = $maxAttempts;
    }

    public function setDecayMinutes(string $key, int $decayMinutes): void
    {
        $this->decayMinutes[$key] = $decayMinutes;
    }

    public function getMaxAttempts(string $key): int
    {
        return $this->maxAttempts[$key] ?? 60;
    }

    public function getDecayMinutes(string $key): int
    {
        return $this->decayMinutes[$key] ?? 1;
    }

    protected function getKey(string $key): string
    {
        return 'rate_limit:' . $key;
    }

    protected function handleFailure(string $key, int $maxAttempts, int $decayMinutes): mixed
    {
        $retryAfter = $this->getRetryAfterDate($key);

        throw new TooManyRequestsHttpException(
            $retryAfter->getTimestamp(),
            "Rate limit exceeded. Please try again in {$decayMinutes} minutes."
        );
    }
}
