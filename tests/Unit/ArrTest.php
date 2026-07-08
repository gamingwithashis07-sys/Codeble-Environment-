<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use LoveGem\Support\Arr;

class ArrTest extends TestCase
{
    public function testArrayDot(): void
    {
        $array = ['user' => ['name' => 'John', 'email' => 'john@example.com']];
        $dot = Arr::dot($array);

        $this->assertEquals('John', $dot['user.name']);
        $this->assertEquals('john@example.com', $dot['user.email']);
    }

    public function testArrayOnly(): void
    {
        $array = ['name' => 'John', 'email' => 'john@example.com', 'age' => 25];
        $only = Arr::only($array, ['name', 'email']);

        $this->assertArrayHasKey('name', $only);
        $this->assertArrayHasKey('email', $only);
        $this->assertArrayNotHasKey('age', $only);
    }

    public function testArrayExcept(): void
    {
        $array = ['name' => 'John', 'email' => 'john@example.com', 'age' => 25];
        $except = Arr::except($array, ['age']);

        $this->assertArrayHasKey('name', $except);
        $this->assertArrayHasKey('email', $except);
        $this->assertArrayNotHasKey('age', $except);
    }

    public function testArrayGet(): void
    {
        $array = ['user' => ['name' => 'John']];

        $this->assertEquals('John', Arr::get($array, 'user.name'));
        $this->assertNull(Arr::get($array, 'user.email'));
        $this->assertEquals('default', Arr::get($array, 'user.email', 'default'));
    }

    public function testArraySet(): void
    {
        $array = [];
        Arr::set($array, 'user.name', 'John');

        $this->assertEquals('John', $array['user']['name']);
    }

    public function testArrayHas(): void
    {
        $array = ['user' => ['name' => 'John']];

        $this->assertTrue(Arr::has($array, 'user.name'));
        $this->assertFalse(Arr::has($array, 'user.email'));
    }

    public function testArrayFlatten(): void
    {
        $array = [['a', 'b'], ['c', 'd']];
        $flat = Arr::flatten($array);

        $this->assertEquals(['a', 'b', 'c', 'd'], $flat);
    }

    public function testArrayPluck(): void
    {
        $array = [
            ['name' => 'John', 'email' => 'john@example.com'],
            ['name' => 'Jane', 'email' => 'jane@example.com'],
        ];

        $names = Arr::pluck($array, 'name');

        $this->assertEquals(['John', 'Jane'], $names);
    }
}
