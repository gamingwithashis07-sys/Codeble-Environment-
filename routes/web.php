<?php

use LoveGem\Http\Routing\Router;

$router = app('router');

$router->get('/', function () {
    return response()->json([
        'name' => 'LoveGem Framework',
        'version' => '1.0.0',
        'description' => 'A privacy-first PHP framework inspired by Laravel',
        'features' => [
            'Service Container',
            'Facades',
            'Eloquent ORM',
            'Blade Templates',
            'Middleware',
            'Routing',
            'Encryption',
            'GDPR Compliance',
        ],
    ]);
});

$router->get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
});

$router->get('/privacy', function () {
    $policy = new LoveGem\Compliance\PrivacyPolicy(
        'LoveGem App',
        'privacy@lovegem.dev',
        30
    );

    $policy->addDataType('Name', 'Your full name');
    $policy->addDataType('Email', 'Your email address');
    $policy->addPurpose('Service Delivery', 'To provide you with our services');

    return response()->json([
        'policy' => $policy->generate(),
    ]);
});
