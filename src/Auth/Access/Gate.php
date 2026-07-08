<?php

declare(strict_types=1);

namespace LoveGem\Auth\Access;

use Closure;
use LoveGem\Core\Application;

class Gate
{
    protected Application $app;

    protected array $abilities = [];

    protected array $policies = [];

    protected array $beforeCallbacks = [];

    protected array $afterCallbacks = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function define(string $ability, callable $callback): void
    {
        $this->abilities[$ability] = $callback;
    }

    public function policy(string $class, string $policy): void
    {
        $this->policies[$class] = $policy;
    }

    public function before(callable $callback): void
    {
        $this->beforeCallbacks[] = $callback;
    }

    public function after(callable $callback): void
    {
        $this->afterCallbacks[] = $callback;
    }

    public function allows(string $ability, mixed ...$arguments): bool
    {
        return $this->check($ability, ...$arguments);
    }

    public function denies(string $ability, mixed ...$arguments): bool
    {
        return !$this->allows($ability, ...$arguments);
    }

    public function check(string $ability, mixed ...$arguments): bool
    {
        foreach ($this->beforeCallbacks as $callback) {
            $result = $callback($this, $ability, $arguments);

            if (!is_null($result)) {
                return $result;
            }
        }

        $result = $this->raw($ability, $arguments);

        foreach ($this->afterCallbacks as $callback) {
            $afterResult = $callback($this, $ability, $result, $arguments);

            if (!is_null($afterResult)) {
                return $afterResult;
            }
        }

        return $result;
    }

    public function raw(string $ability, array $arguments): bool
    {
        if (isset($this->abilities[$ability])) {
            return $this->callCallback($this->abilities[$ability], $arguments);
        }

        return false;
    }

    protected function callCallback(callable $callback, array $arguments): bool
    {
        return (bool) $callback(...$arguments);
    }

    public function getPolicyFor(object $class): ?object
    {
        $class = get_class($class);

        if (isset($this->policies[$class])) {
            $policyClass = $this->policies[$class];

            if (class_exists($policyClass)) {
                return new $policyClass($this->app);
            }
        }

        return null;
    }

    public function getAbilities(): array
    {
        return $this->abilities;
    }

    public function getPolicies(): array
    {
        return $this->policies;
    }
}
