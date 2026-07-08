<?php

declare(strict_types=1);

namespace LoveGem\Container;

use Closure;
use ArrayAccess;
use ReflectionClass;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionParameter;
use InvalidArgumentException;
use RuntimeException;

class Container implements ArrayAccess
{
    protected static ?Container $instance = null;

    protected array $bindings = [];

    protected array $instances = [];

    protected array $aliases = [];

    protected array $abstractAliases = [];

    protected array $extenders = [];

    protected array $tags = [];

    protected array $contextual = [];

    protected array $resolved = [];

    protected array $methodCallbackCache = [];

    protected array $beforeCallback = [];

    protected array $afterCallback = [];

    protected array $with = [];

    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public static function setInstance(?self $container = null): ?self
    {
        return static::$instance = $container;
    }

    public function bound(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) ||
               isset($this->instances[$abstract]) ||
               $this->isAlias($abstract);
    }

    public function alias(string $abstract, string $alias): void
    {
        $this->aliases[$alias] = $abstract;
        $this->abstractAliases[$abstract][] = $alias;
    }

    public function tag(array|string $abstracts, array|string $tags): void
    {
        foreach ((array) $tags as $tag) {
            foreach ((array) $abstracts as $abstract) {
                $this->tags[$tag][] = $this->getAlias($abstract);
            }
        }
    }

    public function tagged(string $tag): array
    {
        $results = [];

        foreach ($this->tags[$tag] ?? [] as $abstract) {
            $results[] = $this->make($abstract);
        }

        return $results;
    }

    public function bind(string|array $abstract, Closure|string|null $concrete = null, bool $shared = false): void
    {
        $this->dropStaleInstances($abstract);

        if (is_array($abstract)) {
            foreach ($abstract as $type) {
                $this->bind($type, $concrete, $shared);
            }
            return;
        }

        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        if (!$concrete instanceof Closure) {
            $concrete = $this->build($concrete);
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');

        if ($this->resolved($abstract)) {
            $this->instances[$abstract] = $this->make($abstract);
        }
    }

    public function bindIf(string|array $abstract, Closure|string|null $concrete = null, bool $shared = false): void
    {
        if (!$this->bound($abstract)) {
            $this->bind($abstract, $concrete, $shared);
        }
    }

    public function singleton(string|array $abstract, Closure|string|null $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    public function singletonIf(string|array $abstract, Closure|string|null $concrete = null): void
    {
        if (!$this->bound($abstract)) {
            $this->singleton($abstract, $concrete);
        }
    }

    public function extend(string $abstract, Closure $closure): void
    {
        $abstract = $this->getAlias($abstract);

        if (isset($this->instances[$abstract])) {
            $this->instances[$abstract] = $closure($this->instances[$abstract], $this);
        } else {
            $this->extenders[$abstract][] = $closure;
        }
    }

    public function instance(string $abstract, mixed $instance): mixed
    {
        $this->removeAbstractAlias($abstract);

        if (is_array($abstract)) {
            foreach ($abstract as $type) {
                $this->instance($type, $instance);
            }
            return $instance;
        }

        $this->instances[$this->getAlias($abstract)] = $instance;

        return $instance;
    }

    public function addContextualBinding(string $concrete, string $abstract, Closure $implementation): void
    {
        $this->contextual[$concrete][$this->getAlias($abstract)] = $implementation;
    }

    public function when(string $abstract): ContextualBindingBuilder
    {
        return new ContextualBindingBuilder($this, $this->getAlias($abstract));
    }

    public function factory(string $abstract): Closure
    {
        return function () use ($abstract) {
            return $this->make($abstract);
        };
    }

    public function flush(): void
    {
        $this->aliases = [];
        $this->bindings = [];
        $this->instances = [];
        $this->extenders = [];
        $this->tags = [];
    }

    public function make(string|array $abstract, array $parameters = []): mixed
    {
        return $this->resolve($abstract, $parameters);
    }

    public function call(array|string $callback, array $parameters = [], ?string $defaultMethod = null): mixed
    {
        if (is_string($callback) && str_contains($callback, '@')) {
            [$class, $method] = Str::parseCallback($callback, $defaultMethod);
            $callback = [$this->make($class), $method ?? $defaultMethod];
        }

        if (is_array($callback) && is_string($callback[0] ?? null)) {
            $callback[0] = $this->make($callback[0]);
        }

        return $callback(...(is_array($parameters) ? $parameters : [$parameters]));
    }

    public function resolving(string $abstract, ?Closure $callback = null): void
    {
        if (!is_null($callback)) {
            $this->beforeCallback[$abstract][] = $callback;
        }
    }

    public function resolved(string|array $abstracts): bool
    {
        foreach ((array) $abstracts as $abstract) {
            if (isset($this->instances[$this->getAlias($abstract)])) {
                return true;
            }
        }

        return false;
    }

    public function afterResolving(string $abstract, ?Closure $callback = null): void
    {
        if (!is_null($callback)) {
            $this->afterCallback[$abstract][] = $callback;
        }
    }

    protected function resolve(string|array $abstract, array $parameters = []): mixed
    {
        if (is_string($abstract)) {
            $abstract = $this->getAlias($abstract);
        }

        $this->with[] = $parameters;

        $concrete = $this->getContextualConcrete($abstract);

        $this->beforeResolving($abstract, $parameters);

        if (isset($this->instances[$abstract]) && empty($parameters)) {
            $object = $this->instances[$abstract];
        } else {
            $object = $this->make(
                $concrete ?? $abstract, $parameters
            );
        }

        foreach ($this->getExtenders($abstract) as $extender) {
            $object = $extender($object, $this);
        }

        if ($this->isShared($abstract) && !isset($this->instances[$abstract])) {
            $this->instances[$abstract] = $object;
        }

        $this->fireAfterResolvingCallbacks($abstract, $object);

        $this->with = [];

        return $object;
    }

    protected function beforeResolving(string $abstract, array $parameters): void
    {
        foreach ($this->beforeCallback[$abstract] ?? [] as $callback) {
            $callback($abstract, $parameters);
        }
    }

    protected function fireAfterResolvingCallbacks(string $abstract, mixed $object): void
    {
        foreach ($this->afterCallback[$abstract] ?? [] as $callback) {
            $callback($object, $this);
        }
    }

    protected function getContextualConcrete(string $abstract): ?Closure
    {
        if (!empty($this->contextual[$abstract])) {
            return end($this->contextual[$abstract]);
        }

        $abstractWithConcrete = $abstract . '.*';

        if (!empty($this->contextual[$abstractWithConcrete])) {
            return end($this->contextual[$abstractWithConcrete]);
        }

        return null;
    }

    protected function getExtenders(string $abstract): array
    {
        return $this->extenders[$this->getAlias($abstract)] ?? [];
    }

    protected function removeAbstractAlias(string $abstract): void
    {
        if (!isset($this->aliases[$abstract])) {
            return;
        }

        unset($this->aliases[$abstract], $this->abstractAliases[$abstract]);
    }

    protected function dropStaleInstances(string $abstract): void
    {
        if (isset($this->instances[$abstract])) {
            unset($this->instances[$abstract]);
        }

        if (isset($this->aliases[$abstract])) {
            unset($this->instances[$this->aliases[$abstract]]);
        }
    }

    protected function isAlias(string $abstract): bool
    {
        return isset($this->aliases[$abstract]);
    }

    public function getAlias(string $abstract): string
    {
        return $this->aliases[$abstract] ?? $abstract;
    }

    protected function isShared(string $abstract): bool
    {
        $abstract = $this->getAlias($abstract);

        return isset($this->bindings[$abstract]['shared']) &&
               $this->bindings[$abstract]['shared'] === true;
    }

    public function build(string|array $abstract, array $parameters = []): mixed
    {
        if ($this->isBuildable($abstract)) {
            return $this->build($this->getBuild($abstract), $parameters);
        }

        if (!$abstract instanceof Closure) {
            $reflector = new ReflectionClass($abstract);

            if (!$reflector->isInstantiable()) {
                $this->throwNotInstantiableException($abstract);
            }

            $constructor = $reflector->getConstructor();

            if (is_null($constructor)) {
                return new $abstract;
            }

            $dependencies = $constructor->getParameters();

            $instances = $this->resolveDependencies(
                $dependencies, $parameters
            );

            return $reflector->newInstanceArgs($instances);
        }

        $dependencies = (new ReflectionFunction($abstract))->getParameters();

        $instances = $this->resolveDependencies($dependencies, $parameters);

        return $abstract(...$instances);
    }

    protected function throwNotInstantiableException(string $concrete): void
    {
        throw new InvalidArgumentException("Target [{$concrete}] is not instantiable.");
    }

    protected function isBuildable(mixed $concrete): bool
    {
        return $concrete instanceof Closure ||
               (is_string($concrete) && isset($this->bindings[$concrete]['concrete']));
    }

    protected function getBuild(string $abstract): Closure|string
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]['concrete'];
        }

        return $abstract;
    }

    protected function resolveDependencies(array $dependencies, array $primitives = []): array
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            if (is_object($param = $this->resolveFoundDependency($dependency, $primitives))) {
                $results[] = $param;
            } elseif ($dependency->isDefaultValueAvailable()) {
                $results[] = $dependency->getDefaultValue();
            } elseif ($dependency->allowsNull() && !$dependency->hasType()) {
                $results[] = null;
            } elseif ($dependency->isOptional()) {
                $results[] = $dependency->getDefaultValue();
            } else {
                $this->throwUnresolvableDependencyException($dependency);
            }
        }

        return $results;
    }

    protected function resolveFoundDependency(ReflectionParameter $dependency, array $primitives): mixed
    {
        $name = $dependency->getName();

        if (isset($primitives[$name])) {
            return $primitives[$name];
        }

        $class = $dependency->getType();

        if (is_null($class)) {
            return $this->resolvePrimitive($dependency);
        }

        if ($class instanceof ReflectionNamedType && !$class->isBuiltin() && $class->getName() !== 'self') {
            try {
                return $this->make($class->getName());
            } catch (\Throwable) {
                return $this->resolvePrimitive($dependency);
            }
        }

        return $this->resolvePrimitive($dependency);
    }

    protected function resolvePrimitive(ReflectionParameter $parameter): mixed
    {
        $abstract = $parameter->getDeclaringClass()?->getName();

        if ($abstract && !empty($this->contextual[$abstract])) {
            return $this->resolveContextual($this->contextual[$abstract], $parameter);
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if ($parameter->allowsNull() && !$parameter->hasType()) {
            return null;
        }

        $this->throwUnresolvableDependencyException($parameter);
    }

    protected function resolveContextual(array $abstracts, ReflectionParameter $parameter): mixed
    {
        $parameterName = ltrim($parameter->getName(), '$');

        if (isset($abstracts[$parameterName])) {
            return $abstracts[$parameterName]($this);
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if ($parameter->allowsNull() && !$parameter->hasType()) {
            return null;
        }

        $this->throwUnresolvableDependencyException($parameter);
    }

    protected function throwUnresolvableDependencyException(ReflectionParameter $parameter): void
    {
        $className = $parameter->getDeclaringClass()?->getName();
        $functionName = $parameter->getDeclaringFunction()->getName();

        throw new RuntimeException(
            "Unresolvable dependency resolving [{$parameter}] in class [{$className}] in method [{$functionName}]."
        );
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }

    public function has(string $id): bool
    {
        return $this->bound($id);
    }

    public function get(string $id): mixed
    {
        return $this->make($id);
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->bound($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->make($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->bind($offset, function () use ($value) {
            return $value;
        });
    }

    public function offsetUnset(mixed $offset): void
    {
        if (isset($this->instances[$offset])) {
            unset($this->instances[$offset]);
        }
    }
}
