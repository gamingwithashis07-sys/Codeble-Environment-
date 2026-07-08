<?php

declare(strict_types=1);

namespace LoveGem\Http;

use LoveGem\Core\Application;

class Kernel
{
    protected Application $app;

    protected array $middleware = [
        \LoveGem\Http\Middleware\EncryptCookies::class,
        \LoveGem\Http\Middleware\ValidatePostSize::class,
        \LoveGem\Http\Middleware\TrimStrings::class,
        \LoveGem\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    protected array $middlewareGroups = [
        'web' => [
            \LoveGem\Http\Middleware\EncryptCookies::class,
            \LoveGem\Http\Middleware\AddQueuedCookiesToResponse::class,
            \LoveGem\Http\Middleware\StartSession::class,
            \LoveGem\Http\Middleware\ShareErrorsFromSession::class,
            \LoveGem\Http\Middleware\VerifyCsrfToken::class,
            \LoveGem\Http\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            \LoveGem\Http\Middleware\ThrottleRequests::class.':60,1',
            \LoveGem\Http\Middleware\SubstituteBindings::class,
        ],
    ];

    protected array $middlewarePriority = [
        \LoveGem\Http\Middleware\TrustHosts::class,
        \LoveGem\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \LoveGem\Http\Middleware\ValidatePostSize::class,
        \LoveGem\Http\Middleware\TrimStrings::class,
        \LoveGem\Http\Middleware\ConvertEmptyStringsToNull::class,
        \LoveGem\Http\Middleware\EncryptCookies::class,
        \LoveGem\Http\Middleware\AddQueuedCookiesToResponse::class,
        \LoveGem\Http\Middleware\StartSession::class,
        \LoveGem\Http\Middleware\ShareErrorsFromSession::class,
        \LoveGem\Http\Middleware\VerifyCsrfToken::class,
        \LoveGem\Http\Middleware\SubstituteBindings::class,
    ];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function handle($request): Response
    {
        $request->setRouteResolver(
            $this->withRouting($request)
        );

        $this->shareErrorsFromSession($request);

        return (new Pipeline($this->app))
            ->send($request)
            ->through($this->middleware)
            ->then($this->getResolver());
    }

    protected function withRouting($request): mixed
    {
        $routes = require base_path('routes/web.php');

        return $routes;
    }

    public function terminate($request, $response): void
    {
        $this->terminateMiddleware($request, $response);
    }

    protected function terminateMiddleware($request, $response): void
    {
        //
    }

    protected function shareErrorsFromSession($request): void
    {
        if ($request->hasSession() && $request->session()->has('errors')) {
            $this->app->make('view')->share(
                'errors', $request->session()->get('errors')
            );
        }
    }

    protected function getResolver(): callable
    {
        return function ($request) {
            return $this->router->dispatch($request);
        };
    }
}
