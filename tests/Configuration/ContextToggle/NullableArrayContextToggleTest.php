<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\ContextToggle\NullableArrayContextToggle;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NullableArrayContextToggleTest extends TestCase
{
    #[Test]
    public function constructorSetsDefaultValues(): void
    {
        $toggle = new NullableArrayContextToggle();

        self::assertNull($toggle->http);
        self::assertNull($toggle->cli);
        self::assertNull($toggle->test);
    }

    #[Test]
    public function constructorSetsCustomValues(): void
    {
        $httpArray = ['host' => 'example.com', 'port' => 80];
        $testArray = ['debug' => true, 'seed' => 12345];

        $toggle = new NullableArrayContextToggle(
            http: $httpArray,
            cli: null,
            test: $testArray,
        );

        self::assertSame($httpArray, $toggle->http);
        self::assertNull($toggle->cli);
        self::assertSame($testArray, $toggle->test);
    }

    #[Test]
    public function invokeReturnsCorrectValueForContext(): void
    {
        $httpArray = ['host' => 'example.com', 'port' => 80];
        $testArray = ['debug' => true, 'seed' => 12345];

        $toggle = new NullableArrayContextToggle(
            http: $httpArray,
            cli: null,
            test: $testArray,
        );

        self::assertSame($httpArray, $toggle(Context::Http));
        self::assertNull($toggle(Context::Cli));
        self::assertSame($testArray, $toggle(Context::Test));
    }

    #[Test]
    public function handlesAllNullValues(): void
    {
        $toggle = new NullableArrayContextToggle(
            http: null,
            cli: null,
            test: null,
        );

        self::assertNull($toggle(Context::Http));
        self::assertNull($toggle(Context::Cli));
        self::assertNull($toggle(Context::Test));
    }

    #[Test]
    public function handlesEmptyArrays(): void
    {
        $toggle = new NullableArrayContextToggle(
            http: [],
            cli: null,
            test: [],
        );

        self::assertSame([], $toggle(Context::Http));
        self::assertNull($toggle(Context::Cli));
        self::assertSame([], $toggle(Context::Test));
    }

    #[Test]
    public function handlesNestedArraysAndNull(): void
    {
        $nested = [
            'level1' => [
                'level2' => [
                    'level3' => 'deep-value',
                ],
            ],
        ];

        $toggle = new NullableArrayContextToggle(
            http: $nested,
            cli: null,
            test: $nested,
        );

        self::assertSame($nested, $toggle(Context::Http));
        self::assertNull($toggle(Context::Cli));
        self::assertSame($nested, $toggle(Context::Test));
    }

    #[Test]
    public function handlesMixedArraysAndNull(): void
    {
        $mixed = [
            'string' => 'value',
            'int' => 42,
            'float' => 3.14,
            'bool' => true,
            'null' => null,
            'array' => [1, 2, 3],
            'object' => new \stdClass(),
        ];

        $toggle = new NullableArrayContextToggle(
            http: null,
            cli: $mixed,
            test: null,
        );

        self::assertNull($toggle(Context::Http));
        self::assertEquals($mixed, $toggle(Context::Cli));
        self::assertNull($toggle(Context::Test));
    }
}
