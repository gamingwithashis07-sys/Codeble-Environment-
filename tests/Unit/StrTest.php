<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use LoveGem\Support\Str;
use LoveGem\Support\Arr;

class StrTest extends TestCase
{
    public function testUuidGeneration(): void
    {
        $uuid = Str::uuid();
        $this->assertNotEmpty($uuid);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid);
    }

    public function testStringOperations(): void
    {
        $string = Str::of('hello world');

        $this->assertEquals('hello world', $string->value());
        $this->assertTrue($string->contains('hello'));
        $this->assertFalse($string->contains('xyz'));
        $this->assertEquals('hello world', $string->lower()->value());
        $this->assertEquals('HELLO WORLD', $string->upper()->value());
        $this->assertEquals('Hello World', $string->title()->value());
    }

    public function testSlug(): void
    {
        $slug = Str::slug('Hello World');
        $this->assertEquals('hello-world', $slug);
    }

    public function testSnakeCase(): void
    {
        $snake = Str::snake('HelloWorld');
        $this->assertEquals('hello_world', $snake);
    }

    public function testCamelCase(): void
    {
        $camel = Str::camel('hello_world');
        $this->assertEquals('helloWorld', $camel);
    }

    public function testStudlyCase(): void
    {
        $studly = Str::studly('hello_world');
        $this->assertEquals('HelloWorld', $studly);
    }

    public function testKebabCase(): void
    {
        $kebab = Str::kebab('HelloWorld');
        $this->assertEquals('hello-world', $kebab);
    }

    public function testLimit(): void
    {
        $limited = Str::limit('This is a long string', 10);
        $this->assertEquals('This is a ...', $limited);
    }

    public function testRandom(): void
    {
        $random = Str::random(10);
        $this->assertEquals(10, strlen($random));
    }
}
