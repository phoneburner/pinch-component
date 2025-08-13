<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\ContextToggle\ArrayContextToggle;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ArrayContextToggleTest extends TestCase
{
    #[Test]
    public function constructorSetsDefaultValues(): void
    {
        $toggle = new ArrayContextToggle();

        self::assertSame([], $toggle->http);
        self::assertSame([], $toggle->cli);
        self::assertSame([], $toggle->test);
    }

    #[Test]
    public function constructorSetsCustomValues(): void
    {
        $http_array = ['host' => 'example.com', 'port' => 80];
        $cli_array = ['verbose' => true, 'timeout' => 300];
        $test_array = ['debug' => true, 'seed' => 12345];

        $toggle = new ArrayContextToggle(
            http: $http_array,
            cli: $cli_array,
            test: $test_array,
        );

        self::assertSame($http_array, $toggle->http);
        self::assertSame($cli_array, $toggle->cli);
        self::assertSame($test_array, $toggle->test);
    }

    #[Test]
    #[DataProvider('contextValueProvider')]
    public function invokeReturnsCorrectValueForContext(Context $context, array $expected): void
    {
        $http_array = ['host' => 'example.com', 'port' => 80];
        $cli_array = ['verbose' => true, 'timeout' => 300];
        $test_array = ['debug' => true, 'seed' => 12345];

        $toggle = new ArrayContextToggle(
            http: $http_array,
            cli: $cli_array,
            test: $test_array,
        );

        $result = $toggle($context);

        self::assertSame($expected, $result);
    }

    public static function contextValueProvider(): \Generator
    {
        $http_array = ['host' => 'example.com', 'port' => 80];
        $cli_array = ['verbose' => true, 'timeout' => 300];
        $test_array = ['debug' => true, 'seed' => 12345];

        yield 'HTTP context returns http array' => [Context::Http, $http_array];
        yield 'CLI context returns cli array' => [Context::Cli, $cli_array];
        yield 'Test context returns test array' => [Context::Test, $test_array];
    }

    #[Test]
    public function handlesEmptyArrays(): void
    {
        $toggle = new ArrayContextToggle(
            http: [],
            cli: ['only-cli'],
            test: [],
        );

        self::assertSame([], $toggle(Context::Http));
        self::assertSame(['only-cli'], $toggle(Context::Cli));
        self::assertSame([], $toggle(Context::Test));
    }

    #[Test]
    public function handlesNestedArrays(): void
    {
        $nested = [
            'level1' => [
                'level2' => [
                    'level3' => 'deep-value',
                ],
            ],
        ];

        $toggle = new ArrayContextToggle(
            http: $nested,
            cli: ['simple'],
            test: $nested,
        );

        self::assertSame($nested, $toggle(Context::Http));
        self::assertSame(['simple'], $toggle(Context::Cli));
        self::assertSame($nested, $toggle(Context::Test));
    }

    #[Test]
    public function handlesNumericArrays(): void
    {
        $toggle = new ArrayContextToggle(
            http: [1, 2, 3, 4, 5],
            cli: ['a', 'b', 'c'],
            test: [true, false, null],
        );

        self::assertSame([1, 2, 3, 4, 5], $toggle(Context::Http));
        self::assertSame(['a', 'b', 'c'], $toggle(Context::Cli));
        self::assertSame([true, false, null], $toggle(Context::Test));
    }

    #[Test]
    public function handlesMixedArrays(): void
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

        $toggle = new ArrayContextToggle(
            http: $mixed,
            cli: [],
            test: $mixed,
        );

        self::assertEquals($mixed, $toggle(Context::Http));
        self::assertSame([], $toggle(Context::Cli));
        self::assertEquals($mixed, $toggle(Context::Test));
    }
}
