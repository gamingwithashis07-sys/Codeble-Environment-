<?php

declare(strict_types=1);

namespace LoveGem\Core;

use Closure;
use LoveGem\Container\Container;
use LoveGem\Support\Str;

class Application extends Container
{
    protected string $basePath;

    protected string $appPath;

    protected string $storagePath;

    protected string $environmentPath;

    protected string $environmentFile = '.env';

    protected string $environmentFileLoaded;

    protected array $loadedConfigurations = [];

    protected array $loadedPackages = [];

    protected array $serviceProviders = [];

    protected array $bootedProviders = [];

    protected bool $booted = false;

    protected array $bootingCallbacks = [];

    protected array $bootedCallbacks = [];

    protected array $terminatingCallbacks = [];

    protected bool $terminated = false;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;

        $this->registerBaseBindings();

        $this->registerBaseServiceProviders();

        $this->registerCoreAliases();
    }

    protected function registerBaseBindings(): void
    {
        static::setInstance($this);

        $this->instance('app', $this);

        $this->instance(Container::class, $this);

        $this->bind('path', fn () => $this->basePath());

        $this->bind('path.base', fn () => $this->basePath());

        $this->bind('path.lang', fn () => $this->langPath());

        $this->bind('path.config', fn () => $this->configPath());

        $this->bind('path.public', fn () => $this->publicPath());

        $this->bind('path.storage', fn () => $this->storagePath());

        $this->bind('path.database', fn () => $this->databasePath());

        $this->bind('path.resources', fn () => $this->resourcePath());

        $this->bind('path.bootstrap', fn () => $this->bootstrapPath());
    }

    protected function registerBaseServiceProviders(): void
    {
        //
    }

    protected function registerCoreAliases(): void
    {
        $aliases = [
            'app'          => [\LoveGem\Core\Application::class],
            'container'    => [\LoveGem\Container\Container::class],
            'router'       => [\LoveGem\Http\Routing\Router::class],
        ];

        foreach ($aliases as $key => $aliases) {
            foreach ((array) $aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }

    public function basePath(string $path = ''): string
    {
        return $this->basePath.($path ? DIRECTORY_SEPARATOR.$path : '');
    }

    public function appPath(string $path = ''): string
    {
        return ($this->appPath ?? $this->basePath('app')).($path ? DIRECTORY_SEPARATOR.$path : '');
    }

    public function configPath(string $path = ''): string
    {
        return $this->basePath.($path ? 'config/'.$path : 'config');
    }

    public function publicPath(string $path = ''): string
    {
        return $this->basePath.($path ? 'public/'.$path : 'public');
    }

    public function storagePath(string $path = ''): string
    {
        return ($this->storagePath ?? $this->basePath('storage')).($path ? DIRECTORY_SEPARATOR.$path : '');
    }

    public function databasePath(string $path = ''): string
    {
        return $this->basePath.($path ? 'database/'.$path : 'database');
    }

    public function langPath(string $path = ''): string
    {
        return $this->basePath.($path ? 'lang/'.$path : 'lang');
    }

    public function resourcePath(string $path = ''): string
    {
        return $this->basePath.($path ? 'resources/'.$path : 'resources');
    }

    public function bootstrapPath(string $path = ''): string
    {
        return $this->basePath.($path ? 'bootstrap/'.$path : 'bootstrap');
    }

    public function environmentFile(): string
    {
        return $this->environmentFile;
    }

    public function environmentFileLoaded(): bool
    {
        return isset($this->environmentFileLoaded);
    }

    public function environmentPath(): string
    {
        return $this->environmentPath ?? $this->basePath;
    }

    public function hasEnvironmentFile(string $file): bool
    {
        return file_exists($this->environmentPath().'/'.$file);
    }

    public function loadEnvironmentFrom(string $file): void
    {
        $this->environmentFile = $file;
    }

    public function environmentPathUsing(Closure $callback): void
    {
        $this->environmentPath = $callback();
    }

    public function loadEnvironmentVariables(): void
    {
        if ($this->environmentFileLoaded) {
            return;
        }

        $this->environmentFileLoaded = true;

        if (!file_exists($this->environmentPath().'/'.$this->environmentFile)) {
            return;
        }

        $dotenv = \Dotenv\Dotenv::createImmutable(
            $this->environmentPath(),
            $this->environmentFile
        );

        $dotenv->safeLoad();
    }

    public function getEnvironment(): string
    {
        return $this['env'] ??= $this->detectEnvironment();
    }

    protected function detectEnvironment(): string
    {
        return $this->environment ??= 'production';
    }

    public function runningInConsole(): bool
    {
        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }

    public function runningUnitTesting(): bool
    {
        return $this->env === 'testing';
    }

    public function isProduction(): bool
    {
        return $this->environment() === 'production';
    }

    public function isLocal(): bool
    {
        return $this->environment() === 'local';
    }

    public function isMaintenanceMode(): bool
    {
        return file_exists($this->storagePath('framework/maintenance.php'));
    }

    public function registerConfiguredProviders(): void
    {
        $providers = $this->make('config')->get('app.providers', []);

        $this->registerProviders($providers);
    }

    public function registerProviders(array $providers): void
    {
        foreach ($providers as $provider) {
            $this->registerProviderInstance(
                $this->resolveProvider($provider)
            );
        }
    }

    protected function resolveProvider(string|object $provider): ServiceProvider
    {
        if (is_string($provider)) {
            return new $provider($this);
        }

        return $provider;
    }

    protected function registerProviderInstance(ServiceProvider $provider): ServiceProvider
    {
        if (isset($this->serviceProviders[get_class($provider)])) {
            return $this->serviceProviders[get_class($provider)];
        }

        return tap($provider, function ($provider) {
            $provider->register();

            $this->serviceProviders[get_class($provider)] = $provider;

            foreach ($provider->provides() as $service) {
                $this->bindings[$service] = $this->bindings[$service] ?? $provider;
            }
        });
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        array_walk($this->serviceProviders, function ($provider) {
            $this->bootProvider($provider);
        });

        $this->booted = true;

        $this->fireAppCallbacks($this->bootedCallbacks);
    }

    protected function bootProvider(ServiceProvider $provider): void
    {
        $provider->boot();

        $this->bootedProviders[get_class($provider)] = $provider;

        $this->fireAppCallbacks($provider->afterResolvingCallbacks($this));
    }

    public function booted(Closure $callback): void
    {
        if ($this->isBooted()) {
            $callback($this);
        } else {
            $this->bootedCallbacks[] = $callback;
        }
    }

    public function isBooted(): bool
    {
        return $this->booted;
    }

    public function booting(Closure $callback): void
    {
        $this->bootingCallbacks[] = $callback;
    }

    public function terminating(Closure $callback): void
    {
        $this->terminatingCallbacks[] = $callback;
    }

    public function terminate(): void
    {
        $this->terminateMiddleware();

        $this->terminateExceptionHandler();

        $this->fireAppCallbacks($this->terminatingCallbacks);

        $this->terminated = true;
    }

    protected function terminateMiddleware(): void
    {
        //
    }

    protected function terminateExceptionHandler(): void
    {
        //
    }

    public function isTerminated(): bool
    {
        return $this->terminated;
    }

    protected function fireAppCallbacks(array $callbacks): void
    {
        if (!empty($callbacks)) {
            foreach ($callbacks as $callback) {
                $callback($this);
            }
        }
    }

    public function addAbsoluteMergePath(string $path): void
    {
        $this->absoluteMergePaths[] = $path;
    }

    public function getAbsoluteMergePaths(): array
    {
        return $this->absoluteMergePaths ?? [];
    }

    public function getMaintenanceMode(): int|bool
    {
        return $this->maintenanceMode ?? false;
    }

    public function setMaintenanceMode(int|bool $value): void
    {
        $this->maintenanceMode = $value;
    }

    public function shouldPreventRequestsDuringMaintenance(): bool
    {
        return $this->getMaintenanceMode() !== false;
    }

    public function getNamespace(): string
    {
        return $this->namespace ?? ('App\\');
    }

    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale ?? 'en';
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getFallbackLocale(): string
    {
        return $this->fallbackLocale ?? 'en';
    }

    public function setFallbackLocale(string $fallbackLocale): void
    {
        $this->fallbackLocale = $fallbackLocale;
    }

    public function getCurrencyLocale(): string
    {
        return $this->currencyLocale ?? 'en';
    }

    public function setCurrencyLocale(string $currencyLocale): void
    {
        $this->currencyLocale = $currencyLocale;
    }

    public function getDriver(): string
    {
        return $this->driver ?? 'database';
    }

    public function setDriver(string $driver): void
    {
        $this->driver = $driver;
    }

    public function getArtisan(): ?Artisan
    {
        return $this->artisan ?? null;
    }

    public function setArtisan(Artisan $artisan): void
    {
        $this->artisan = $artisan;
    }

    public function getConfigurationRepository(): Repository
    {
        return $this->make('config');
    }

    public function setConfigurationRepository(Repository $config): void
    {
        $this->instance('config', $config);
    }

    public function getEventDispatcher(): Dispatcher
    {
        return $this->make('events');
    }

    public function setEventDispatcher(Dispatcher $events): void
    {
        $this->instance('events', $events);
    }

    public function getExceptionHandler(): Handler
    {
        return $this->make(ExceptionHandler::class);
    }

    public function setExceptionHandler(Handler $handler): void
    {
        $this->instance(ExceptionHandler::class, $handler);
    }

    public function flushProviders(): void
    {
        $this->serviceProviders = [];
        $this->bootedProviders = [];
    }

    public function getLoadedProviders(): array
    {
        return $this->serviceProviders;
    }

    public function getProviderCount(): int
    {
        return count($this->serviceProviders);
    }

    public function getBootedProviders(): array
    {
        return $this->bootedProviders;
    }
}
