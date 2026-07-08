<?php

declare(strict_types=1);

namespace LoveGem\Api;

use LoveGem\Core\Application;
use LoveGem\Support\Str;

class Sanctum
{
    protected Application $app;

    protected static ?object $actingAs = null;

    protected static array $actingAsAbilities = ['*'];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public static function createApiToken(object $user, array $abilities = ['*']): array
    {
        $token = Str::random(60);

        $plainTextToken = hash('sha256', $token);

        $user->apiTokens()->create([
            'name' => 'api_token',
            'token' => $plainTextToken,
            'abilities' => json_encode($abilities),
        ]);

        return [
            'plainTextToken' => $plainTextToken,
            'accessToken' => $token,
        ];
    }

    public static function createTestToken(object $user, array $abilities = ['*']): string
    {
        $token = Str::random(60);
        $plainTextToken = hash('sha256', $token);

        $user->apiTokens()->create([
            'name' => 'test_token',
            'token' => $plainTextToken,
            'abilities' => json_encode($abilities),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $plainTextToken;
    }

    public static function revokeApiToken(object $user, string $token): bool
    {
        return (bool) $user->apiTokens()
            ->where('token', hash('sha256', $token))
            ->delete();
    }

    public static function revokeAllApiTokens(object $user): bool
    {
        return (bool) $user->apiTokens()->delete();
    }

    public static function actingAs(object $user, array $abilities = ['*']): void
    {
        static::$actingAs = $user;
        static::$actingAsAbilities = $abilities;
    }

    public static function assertAuthenticatedAs(object $user): void
    {
        if (static::actingAs() !== $user) {
            throw new \RuntimeException('User is not authenticated as expected.');
        }
    }

    public static function getActingAs(): ?object
    {
        return static::$actingAs;
    }

    public static function hasAbility(object $user, string $ability): bool
    {
        $abilities = json_decode($user->currentAccessToken()->abilities ?? '[]', true);

        return in_array($ability, $abilities) || in_array('*', $abilities);
    }
}
