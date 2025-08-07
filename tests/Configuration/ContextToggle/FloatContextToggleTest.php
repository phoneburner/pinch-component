<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\ContextToggle\FloatContextToggle;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FloatContextToggleTest extends TestCase
{
    #[Test]
    public function constructorSetsDefaultValues(): void
    {
        $toggle = new FloatContextToggle();

        self::assertSame(0.0, $toggle->http);
        self::assertSame(0.0, $toggle->cli);
        self::assertSame(0.0, $toggle->test);
    }

    #[Test]
    public function constructorSetsCustomValues(): void
    {
        $toggle = new FloatContextToggle(
            http: 3.14159,
            cli: -2.718,
            test: 1.414,
        );

        self::assertSame(3.14159, $toggle->http);
        self::assertSame(-2.718, $toggle->cli);
        self::assertSame(1.414, $toggle->test);
    }

    #[Test]
    #[DataProvider('contextValueProvider')]
    public function invokeReturnsCorrectValueForContext(Context $context, float $expected): void
    {
        $toggle = new FloatContextToggle(
            http: 3.14159,
            cli: -2.718,
            test: 1.414,
        );

        $result = $toggle($context);

        self::assertSame($expected, $result);
    }

    public static function contextValueProvider(): \Generator
    {
        yield 'HTTP context returns 3.14159' => [Context::Http, 3.14159];
        yield 'CLI context returns -2.718' => [Context::Cli, -2.718];
        yield 'Test context returns 1.414' => [Context::Test, 1.414];
    }

    #[Test]
    public function handlesNegativeValues(): void
    {
        $toggle = new FloatContextToggle(
            http: -1.5,
            cli: -100.999,
            test: -0.001,
        );

        self::assertSame(-1.5, $toggle(Context::Http));
        self::assertSame(-100.999, $toggle(Context::Cli));
        self::assertSame(-0.001, $toggle(Context::Test));
    }

    #[Test]
    public function handlesExtremeValues(): void
    {
        $toggle = new FloatContextToggle(
            http: \PHP_FLOAT_MAX,
            cli: \PHP_FLOAT_MIN,
            test: \PHP_FLOAT_EPSILON,
        );

        self::assertSame(\PHP_FLOAT_MAX, $toggle(Context::Http));
        self::assertSame(\PHP_FLOAT_MIN, $toggle(Context::Cli));
        self::assertSame(\PHP_FLOAT_EPSILON, $toggle(Context::Test));
    }

    #[Test]
    public function handlesSpecialFloatValues(): void
    {
        $toggle = new FloatContextToggle(
            http: \INF,
            cli: -\INF,
            test: \NAN,
        );

        self::assertSame(\INF, $toggle(Context::Http));
        self::assertSame(-\INF, $toggle(Context::Cli));
        self::assertNan($toggle(Context::Test));
    }

    #[Test]
    public function handlesIntegerPromotedToFloat(): void
    {
        $toggle = new FloatContextToggle(
            http: 42,
            cli: -17,
            test: 0,
        );

        self::assertSame(42.0, $toggle(Context::Http));
        self::assertSame(-17.0, $toggle(Context::Cli));
        self::assertSame(0.0, $toggle(Context::Test));
    }
}
