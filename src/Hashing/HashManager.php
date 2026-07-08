<?php

declare(strict_types=1);

namespace LoveGem\Hashing;

class HashManager
{
    protected string $driver = 'bcrypt';

    protected int $rounds = 12;

    public function make(string $value, array $options = []): string
    {
        return match ($this->driver) {
            'bcrypt' => $this->bcrypt($value, $options),
            'argon2i' => $this->argon2i($value, $options),
            'argon2id' => $this->argon2id($value, $options),
            default => $this->bcrypt($value, $options),
        };
    }

    public function check(string $value, string $hashedValue, array $options = []): bool
    {
        return password_verify($value, $hashedValue);
    }

    public function needsRehash(string $hashedValue, array $options = []): bool
    {
        return password_needs_rehash($hashedValue, PASSWORD_BCRYPT, [
            'cost' => $this->rounds,
        ]);
    }

    protected function bcrypt(string $value, array $options = []): string
    {
        $cost = $options['cost'] ?? $this->rounds;

        return password_hash($value, PASSWORD_BCRYPT, ['cost' => $cost]);
    }

    protected function argon2i(string $value, array $options = []): string
    {
        return password_hash($value, PASSWORD_ARGON2I, [
            'memory_cost' => $options['memory'] ?? 65536,
            'time_cost' => $options['time'] ?? 4,
            'threads' => $options['threads'] ?? 1,
        ]);
    }

    protected function argon2id(string $value, array $options = []): string
    {
        return password_hash($value, PASSWORD_ARGON2ID, [
            'memory_cost' => $options['memory'] ?? 65536,
            'time_cost' => $options['time'] ?? 4,
            'threads' => $options['threads'] ?? 1,
        ]);
    }

    public function setDriver(string $driver): void
    {
        $this->driver = $driver;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    public function setRounds(int $rounds): void
    {
        $this->rounds = $rounds;
    }

    public function info(string $hashedValue): ?array
    {
        return password_get_info($hashedValue);
    }
}
