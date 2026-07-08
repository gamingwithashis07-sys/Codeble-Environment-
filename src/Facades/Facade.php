<?php

declare(strict_types=1);

namespace LoveGem\Facades;

use Closure;
use RuntimeException;
use LoveGem\Container\Container;

abstract class Facade
{
    protected static array $resolvedInstances = [];

    protected static Container $app;

    protected static array $macros = [];

    protected static ?Closure $swapCallback = null;

    public static function getFacadeAccessor(): string|object
    {
        throw new RuntimeException('Facade accessor is not defined.');
    }

    public static function getFacadeRoot(): mixed
    {
        return static::resolveFacadeInstance(static::getFacadeAccessor());
    }

    public static function swap(Closure $callback): void
    {
        static::$swapCallback = $callback;
    }

    protected static function resolveFacadeInstance(string|object $name): mixed
    {
        if (isset(static::$resolvedInstances[$name])) {
            return static::$resolvedInstances[$name];
        }

        if (static::$app) {
            if (isset(static::$swapCallback)) {
                return static::$swapCallback(static::$app, $name);
            }

            return static::$resolvedInstances[$name] = static::$app[$name];
        }
    }

    protected static function getRootContainer(): Container
    {
        return static::$app ??= Container::getInstance();
    }

    public static function clearResolvedInstances(): void
    {
        static::$resolvedInstances = [];
    }

    public static function shouldForwardCalls(): bool
    {
        return true;
    }

    public static function macro(string $name, callable $macro): void
    {
        $class = static::getFacadeAccessor();

        if (!isset(static::$macros[$class])) {
            static::$macros[$class] = [];
        }

        static::$macros[$class][$name] = $macro;
    }

    public static function hasMacro(string $name): bool
    {
        $class = static::getFacadeAccessor();

        return isset(static::$macros[$class][$name]);
    }

    public static function __callStatic(string $method, array $args): mixed
    {
        $instance = static::getFacadeRoot();

        if (!$instance) {
            throw new RuntimeException('A facade root has not been set.');
        }

        if (isset(static::$macros[$class = static::getFacadeAccessor()][$method])) {
            $macro = static::$macros[$class][$method];

            if ($macro instanceof Closure) {
                $macro = $macro->bindTo(null, static::class);
            }

            return $macro(...$args);
        }

        if (static::shouldForwardCalls()) {
            return $instance->{$method}(...$args);
        }

        throw new RuntimeException("Method [{$method}] does not exist on the facade.");
    }
}
