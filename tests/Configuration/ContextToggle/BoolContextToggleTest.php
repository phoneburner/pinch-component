<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\ContextToggle\BoolContextToggle;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BoolContextToggleTest extends TestCase
{
    #[Test]
    public function constructorSetsDefaultValues(): void
    {
        $toggle = new BoolContextToggle();

        self::assertFalse($toggle->http);
        self::assertFalse($toggle->cli);
        self::assertFalse($toggle->test);
    }

    #[Test]
    public function constructorSetsCustomValues(): void
    {
        $toggle = new BoolContextToggle(
            http: true,
            cli: false,
            test: true,
        );

        self::assertTrue($toggle->http);
        self::assertFalse($toggle->cli);
        self::assertTrue($toggle->test);
    }

    #[Test]
    #[DataProvider('contextValueProvider')]
    public function invokeReturnsCorrectValueForContext(Context $context, bool $expected): void
    {
        $toggle = new BoolContextToggle(
            http: true,
            cli: false,
            test: true,
        );

        $result = $toggle($context);

        self::assertSame($expected, $result);
    }

    public static function contextValueProvider(): \Generator
    {
        yield 'HTTP context returns true' => [Context::Http, true];
        yield 'CLI context returns false' => [Context::Cli, false];
        yield 'Test context returns true' => [Context::Test, true];
    }

    #[Test]
    public function allContextsCanHaveSameValue(): void
    {
        $toggle = new BoolContextToggle(
            http: true,
            cli: true,
            test: true,
        );

        self::assertTrue($toggle(Context::Http));
        self::assertTrue($toggle(Context::Cli));
        self::assertTrue($toggle(Context::Test));
    }

    #[Test]
    public function allContextsCanHaveDifferentValues(): void
    {
        $toggle = new BoolContextToggle(
            http: true,
            cli: false,
            test: true,
        );

        self::assertTrue($toggle(Context::Http));
        self::assertFalse($toggle(Context::Cli));
        self::assertTrue($toggle(Context::Test));
    }
}
