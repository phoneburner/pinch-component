<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\ContextToggle\NullableBoolContextToggle;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NullableBoolContextToggleTest extends TestCase
{
    #[Test]
    public function constructorSetsDefaultValues(): void
    {
        $toggle = new NullableBoolContextToggle();

        self::assertNull($toggle->http);
        self::assertNull($toggle->cli);
        self::assertNull($toggle->test);
    }

    #[Test]
    public function constructorSetsCustomValues(): void
    {
        $toggle = new NullableBoolContextToggle(
            http: true,
            cli: null,
            test: false,
        );

        self::assertTrue($toggle->http);
        self::assertNull($toggle->cli);
        self::assertFalse($toggle->test);
    }

    #[Test]
    #[DataProvider('contextValueProvider')]
    public function invokeReturnsCorrectValueForContext(Context $context, bool|null $expected): void
    {
        $toggle = new NullableBoolContextToggle(
            http: true,
            cli: null,
            test: false,
        );

        $result = $toggle($context);

        self::assertSame($expected, $result);
    }

    public static function contextValueProvider(): \Generator
    {
        yield 'HTTP context returns true' => [Context::Http, true];
        yield 'CLI context returns null' => [Context::Cli, null];
        yield 'Test context returns false' => [Context::Test, false];
    }

    #[Test]
    public function handlesAllNullValues(): void
    {
        $toggle = new NullableBoolContextToggle(
            http: null,
            cli: null,
            test: null,
        );

        self::assertNull($toggle(Context::Http));
        self::assertNull($toggle(Context::Cli));
        self::assertNull($toggle(Context::Test));
    }

    #[Test]
    public function handlesAllBooleanValues(): void
    {
        $toggle = new NullableBoolContextToggle(
            http: true,
            cli: false,
            test: true,
        );

        self::assertTrue($toggle(Context::Http));
        self::assertFalse($toggle(Context::Cli));
        self::assertTrue($toggle(Context::Test));
    }

    #[Test]
    public function handlesMixedNullAndBooleanValues(): void
    {
        $toggle = new NullableBoolContextToggle(
            http: null,
            cli: true,
            test: null,
        );

        self::assertNull($toggle(Context::Http));
        self::assertTrue($toggle(Context::Cli));
        self::assertNull($toggle(Context::Test));
    }
}
