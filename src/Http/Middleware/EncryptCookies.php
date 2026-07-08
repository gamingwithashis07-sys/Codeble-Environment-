<?php

declare(strict_types=1);

namespace LoveGem\Http\Middleware;

use Closure;
use LoveGem\Http\Request;

class EncryptCookies
{
    protected array $except = [];

    public function handle(Request $request, Closure $next): mixed
    {
        return $next($request);
    }
}
