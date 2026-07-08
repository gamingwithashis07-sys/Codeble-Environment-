<?php

declare(strict_types=1);

namespace LoveGem\Auth;

interface Guard
{
    public function id();

    public function check(): bool;

    public function guest(): bool;

    public function user(): ?object;

    public function validate(array $credentials = []): bool;

    public function attempt(array $credentials, bool $remember = false): bool;

    public function login(object $user, bool $remember = false): void;

    public function logout(): void;

    public function loginUsingId(mixed $id, bool $remember = false): ?object;

    public function onceUsingId(mixed $id): ?object;

    public function viaRememberToken(): bool;
}
