<?php

declare(strict_types=1);

namespace LoveGem\Support\Pipeline;

use Closure;

class Pipeline
{
    protected mixed $passable;

    protected array $pipes = [];

    protected string $method = 'handle';

    public function __construct(mixed $passable)
    {
        $this->passable = $passable;
    }

    public function send(mixed $passable): static
    {
        $this->passable = $passable;

        return $this;
    }

    public function through(array|callable $pipes): static
    {
        $this->pipes = is_array($pipes) ? $pipes : [$pipes];

        return $this;
    }

    public function via(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function then(callable $destination): mixed
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes),
            $this->carry(),
            $this->prepareDestination($destination)
        );

        return $pipeline($this->passable);
    }

    public function thenReturn(): mixed
    {
        return $this->then(function ($passable) {
            return $passable;
        });
    }

    protected function carry(): Closure
    {
        return function ($next, $pipe) {
            return function ($passable) use ($next, $pipe) {
                if ($pipe instanceof Closure) {
                    return $pipe($passable, $next);
                } elseif (is_object($pipe)) {
                    return $pipe->{$this->method}($passable, $next);
                } elseif (is_string($pipe)) {
                    return $this->classPipeline($pipe, $passable, $next);
                }

                throw new \InvalidArgumentException("Invalid pipe type: " . gettype($pipe));
            };
        };
    }

    protected function classPipeline(string $pipe, mixed $passable, Closure $next): mixed
    {
        if (method_exists($pipe, $this->method)) {
            return (new $pipe)->{$this->method}($passable, $next);
        }

        if (method_exists($pipe, '__invoke')) {
            return (new $pipe)($passable, $next);
        }

        throw new \InvalidArgumentException("Pipe [{$pipe}] must have a handle method.");
    }

    protected function prepareDestination(callable $destination): Closure
    {
        return function ($passable) use ($destination) {
            return $destination($passable);
        };
    }
}
