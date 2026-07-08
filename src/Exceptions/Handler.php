<?php

declare(strict_types=1);

namespace LoveGem\Exceptions;

use Throwable;
use LoveGem\Http\Request;
use LoveGem\Http\Response;

class Handler
{
    protected array $dontReport = [];

    protected array $dontFlash = [
        'password',
        'password_confirmation',
    ];

    protected array $callbacks = [];

    public function register(): void
    {
        //
    }

    public function report(Throwable $e): void
    {
        if ($this->shouldntReport($e)) {
            return;
        }

        $this->logException($e);
    }

    protected function shouldntReport(Throwable $e): bool
    {
        $dontReport = array_merge(
            $this->dontReport,
            [
                \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
                \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException::class,
            ]
        );

        foreach ($dontReport as $type) {
            if ($e instanceof $type) {
                return true;
            }
        }

        return false;
    }

    protected function logException(Throwable $e): void
    {
        $logger = app('log');

        if ($logger) {
            $logger->error($e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }

    public function render(Request $request, Throwable $e): Response
    {
        $response = $this->renderException($request, $e);

        return $response;
    }

    protected function renderException(Request $request, Throwable $e): Response
    {
        if ($this->isHttpException($e)) {
            return $this->renderHttpException($request, $e);
        }

        if ($request->expectsJson()) {
            return $this->renderJsonException($e);
        }

        return $this->renderDefaultException($e);
    }

    protected function renderHttpException(Request $request, \Exception $e): Response
    {
        $status = $e->getStatusCode();
        $message = $e->getMessage() ?: $this->getStatusCodeMessage($status);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'status' => $status,
            ], $status);
        }

        $view = $this->findErrorView($status);

        if ($view) {
            return response()->view($view, [
                'exception' => $e,
                'message' => $message,
                'status' => $status,
            ], $status);
        }

        return response()->view('errors.default', [
            'exception' => $e,
            'message' => $message,
            'status' => $status,
        ], $status);
    }

    protected function renderJsonException(Throwable $e): Response
    {
        return response()->json([
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'trace' => explode("\n", $e->getTraceAsString()),
        ], 500);
    }

    protected function renderDefaultException(Throwable $e): Response
    {
        $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

        return response()->view('errors.default', [
            'exception' => $e,
            'message' => $e->getMessage(),
            'status' => $status,
        ], $status);
    }

    protected function isHttpException(Throwable $e): bool
    {
        return $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException;
    }

    protected function getStatusCodeMessage(int $status): string
    {
        $messages = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            408 => 'Request Timeout',
            419 => 'Page Expired',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
        ];

        return $messages[$status] ?? 'Server Error';
    }

    protected function findErrorView(int $status): ?string
    {
        $view = resource_path("views/errors/{$status}.blade.php");

        if (file_exists($view)) {
            return "errors.{$status}";
        }

        return null;
    }

    public function renderForConsole(Throwable $e): void
    {
        $this->report($e);

        echo $e->getMessage() . PHP_EOL;
        echo $e->getTraceAsString() . PHP_EOL;
    }

    public function renderConsole(Throwable $e): void
    {
        $this->renderForConsole($e);
    }
}
