<?php

declare(strict_types=1);

use LoveGem\Core\Application;

define('LOVEGEM_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(LoveGem\Http\Kernel::class);

$response = $kernel->handle(
    $request = LoveGem\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
