<?php

use LoveGem\Core\Application;
use LoveGem\Http\Kernel;

$app = new Application($_SERVER['APP_BASE_PATH'] ?? dirname(__DIR__));

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = LoveGem\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
