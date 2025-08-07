<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\ContextToggle\NullableIntContextToggle;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NullableIntContextToggleTest extends TestCase
{
    #[Test]
    public function constructorSetsDefaultValues(): void
    {
        $toggle = new NullableIntContextToggle();

        self::assertNull($toggle->http);
        self::assertNull($toggle->cli);
        self::assertNull($toggle->test);
    }

    #[Test]
    public function constructorSetsCustomValues(): void
    {
        $toggle = new NullableIntContextToggle(
            http: 100,
            cli: null,
            test: -50,
        );

        self::assertSame(100, $toggle->http);
        self::assertNull($toggle->cli);
        self::assertSame(-50, $toggle->test);
    }

    #[Test]
    #[DataProvider('contextValueProvider')]
    public function invokeReturnsCorrectValueForContext(Context $context, int|null $expected): void
    {
        $toggle = new NullableIntContextToggle(
            http: 100,
            cli: null,
            test: -50,
        );

        $result = $toggle($context);

        self::assertSame($expected, $result);
    }

    public static function contextValueProvider(): \Generator
    {
        yield 'HTTP context returns 100' => [Context::Http, 100];
        yield 'CLI context returns null' => [Context::Cli, null];
        yield 'Test context returns -50' => [Context::Test, -50];
    }

    #[Test]
    public function handlesAllNullValues(): void
    {
        $toggle = new NullableIntContextToggle(
            http: null,
            cli: null,
            test: null,
        );

        self::assertNull($toggle(Context::Http));
        self::assertNull($toggle(Context::Cli));
        self::assertNull($toggle(Context::Test));
    }

    #[Test]
    public function handlesExtremeIntegerValues(): void
    {
        $toggle = new NullableIntContextToggle(
            http: \PHP_INT_MAX,
            cli: \PHP_INT_MIN,
            test: null,
        );

        self::assertSame(\PHP_INT_MAX, $toggle(Context::Http));
        self::assertSame(\PHP_INT_MIN, $toggle(Context::Cli));
        self::assertNull($toggle(Context::Test));
    }

    #[Test]
    public function handlesZeroValue(): void
    {
        $toggle = new NullableIntContextToggle(
            http: 0,
            cli: null,
            test: 0,
        );

        self::assertSame(0, $toggle(Context::Http));
        self::assertNull($toggle(Context::Cli));
        self::assertSame(0, $toggle(Context::Test));
    }
}
