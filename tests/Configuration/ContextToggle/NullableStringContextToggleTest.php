<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\ContextToggle\NullableStringContextToggle;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NullableStringContextToggleTest extends TestCase
{
    #[Test]
    public function constructorSetsDefaultValues(): void
    {
        $toggle = new NullableStringContextToggle();

        self::assertNull($toggle->http);
        self::assertNull($toggle->cli);
        self::assertNull($toggle->test);
    }

    #[Test]
    public function constructorSetsCustomValues(): void
    {
        $toggle = new NullableStringContextToggle(
            http: 'web-server',
            cli: null,
            test: 'test-environment',
        );

        self::assertSame('web-server', $toggle->http);
        self::assertNull($toggle->cli);
        self::assertSame('test-environment', $toggle->test);
    }

    #[Test]
    #[DataProvider('contextValueProvider')]
    public function invokeReturnsCorrectValueForContext(Context $context, string|null $expected): void
    {
        $toggle = new NullableStringContextToggle(
            http: 'web-server',
            cli: null,
            test: 'test-environment',
        );

        $result = $toggle($context);

        self::assertSame($expected, $result);
    }

    public static function contextValueProvider(): \Generator
    {
        yield 'HTTP context returns web-server' => [Context::Http, 'web-server'];
        yield 'CLI context returns null' => [Context::Cli, null];
        yield 'Test context returns test-environment' => [Context::Test, 'test-environment'];
    }

    #[Test]
    public function handlesAllNullValues(): void
    {
        $toggle = new NullableStringContextToggle(
            http: null,
            cli: null,
            test: null,
        );

        self::assertNull($toggle(Context::Http));
        self::assertNull($toggle(Context::Cli));
        self::assertNull($toggle(Context::Test));
    }

    #[Test]
    public function handlesEmptyStrings(): void
    {
        $toggle = new NullableStringContextToggle(
            http: '',
            cli: null,
            test: '',
        );

        self::assertSame('', $toggle(Context::Http));
        self::assertNull($toggle(Context::Cli));
        self::assertSame('', $toggle(Context::Test));
    }

    #[Test]
    public function handlesMixedStringAndNullValues(): void
    {
        $toggle = new NullableStringContextToggle(
            http: null,
            cli: 'command-line',
            test: null,
        );

        self::assertNull($toggle(Context::Http));
        self::assertSame('command-line', $toggle(Context::Cli));
        self::assertNull($toggle(Context::Test));
    }

    #[Test]
    public function handlesSpecialCharactersAndNull(): void
    {
        $toggle = new NullableStringContextToggle(
            http: 'unicode: 🚀🔥💯',
            cli: null,
            test: "multiline\nstring\twith\ttabs",
        );

        self::assertSame('unicode: 🚀🔥💯', $toggle(Context::Http));
        self::assertNull($toggle(Context::Cli));
        self::assertSame("multiline\nstring\twith\ttabs", $toggle(Context::Test));
    }
}
