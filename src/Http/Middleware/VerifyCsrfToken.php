<?php

declare(strict_types=1);

namespace LoveGem\Http\Middleware;

use Closure;
use LoveGem\Http\Request;

class VerifyCsrfToken
{
    protected array $except = [];

    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->method() === 'POST') {
            $token = $request->input('_token') ?? $request->header('X-CSRF-TOKEN');

            if (!$token || $token !== $request->session()->token()) {
                abort(419, 'CSRF token mismatch.');
            }
        }

        return $next($request);
    }

    protected function tokensMatch($request): bool
    {
        $token = $request->input('_token') ?? $request->header('X-CSRF-TOKEN');

        if (!$token) {
            return false;
        }

        return $token === $request->session()->token();
    }
}
