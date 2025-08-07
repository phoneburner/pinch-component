<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\ContextToggle\IntContextToggle;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IntContextToggleTest extends TestCase
{
    #[Test]
    public function constructorSetsDefaultValues(): void
    {
        $toggle = new IntContextToggle();

        self::assertSame(0, $toggle->http);
        self::assertSame(0, $toggle->cli);
        self::assertSame(0, $toggle->test);
    }

    #[Test]
    public function constructorSetsCustomValues(): void
    {
        $toggle = new IntContextToggle(
            http: 100,
            cli: -50,
            test: 999,
        );

        self::assertSame(100, $toggle->http);
        self::assertSame(-50, $toggle->cli);
        self::assertSame(999, $toggle->test);
    }

    #[Test]
    #[DataProvider('contextValueProvider')]
    public function invokeReturnsCorrectValueForContext(Context $context, int $expected): void
    {
        $toggle = new IntContextToggle(
            http: 100,
            cli: -50,
            test: 999,
        );

        $result = $toggle($context);

        self::assertSame($expected, $result);
    }

    public static function contextValueProvider(): \Generator
    {
        yield 'HTTP context returns 100' => [Context::Http, 100];
        yield 'CLI context returns -50' => [Context::Cli, -50];
        yield 'Test context returns 999' => [Context::Test, 999];
    }

    #[Test]
    public function handlesNegativeValues(): void
    {
        $toggle = new IntContextToggle(
            http: -1,
            cli: -100,
            test: -999,
        );

        self::assertSame(-1, $toggle(Context::Http));
        self::assertSame(-100, $toggle(Context::Cli));
        self::assertSame(-999, $toggle(Context::Test));
    }

    #[Test]
    public function handlesLargeValues(): void
    {
        $toggle = new IntContextToggle(
            http: \PHP_INT_MAX,
            cli: \PHP_INT_MIN,
            test: 0,
        );

        self::assertSame(\PHP_INT_MAX, $toggle(Context::Http));
        self::assertSame(\PHP_INT_MIN, $toggle(Context::Cli));
        self::assertSame(0, $toggle(Context::Test));
    }
}
