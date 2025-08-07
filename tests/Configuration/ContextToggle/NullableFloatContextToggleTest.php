<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\ContextToggle\NullableFloatContextToggle;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NullableFloatContextToggleTest extends TestCase
{
    #[Test]
    public function constructorSetsDefaultValues(): void
    {
        $toggle = new NullableFloatContextToggle();

        self::assertNull($toggle->http);
        self::assertNull($toggle->cli);
        self::assertNull($toggle->test);
    }

    #[Test]
    public function constructorSetsCustomValues(): void
    {
        $toggle = new NullableFloatContextToggle(
            http: 3.14159,
            cli: null,
            test: -2.718,
        );

        self::assertSame(3.14159, $toggle->http);
        self::assertNull($toggle->cli);
        self::assertSame(-2.718, $toggle->test);
    }

    #[Test]
    #[DataProvider('contextValueProvider')]
    public function invokeReturnsCorrectValueForContext(Context $context, float|null $expected): void
    {
        $toggle = new NullableFloatContextToggle(
            http: 3.14159,
            cli: null,
            test: -2.718,
        );

        $result = $toggle($context);

        self::assertSame($expected, $result);
    }

    public static function contextValueProvider(): \Generator
    {
        yield 'HTTP context returns 3.14159' => [Context::Http, 3.14159];
        yield 'CLI context returns null' => [Context::Cli, null];
        yield 'Test context returns -2.718' => [Context::Test, -2.718];
    }

    #[Test]
    public function handlesAllNullValues(): void
    {
        $toggle = new NullableFloatContextToggle(
            http: null,
            cli: null,
            test: null,
        );

        self::assertNull($toggle(Context::Http));
        self::assertNull($toggle(Context::Cli));
        self::assertNull($toggle(Context::Test));
    }

    #[Test]
    public function handlesExtremeFloatValues(): void
    {
        $toggle = new NullableFloatContextToggle(
            http: \PHP_FLOAT_MAX,
            cli: \PHP_FLOAT_MIN,
            test: null,
        );

        self::assertSame(\PHP_FLOAT_MAX, $toggle(Context::Http));
        self::assertSame(\PHP_FLOAT_MIN, $toggle(Context::Cli));
        self::assertNull($toggle(Context::Test));
    }

    #[Test]
    public function handlesSpecialFloatValues(): void
    {
        $toggle = new NullableFloatContextToggle(
            http: \INF,
            cli: null,
            test: \NAN,
        );

        self::assertSame(\INF, $toggle(Context::Http));
        self::assertNull($toggle(Context::Cli));
        self::assertNan($toggle(Context::Test));
    }

    #[Test]
    public function handlesZeroValue(): void
    {
        $toggle = new NullableFloatContextToggle(
            http: 0.0,
            cli: null,
            test: -0.0,
        );

        self::assertSame(0.0, $toggle(Context::Http));
        self::assertNull($toggle(Context::Cli));
        self::assertSame(-0.0, $toggle(Context::Test));
    }
}
