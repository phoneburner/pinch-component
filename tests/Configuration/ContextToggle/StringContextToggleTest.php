<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\ContextToggle\StringContextToggle;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class StringContextToggleTest extends TestCase
{
    #[Test]
    public function constructorSetsDefaultValues(): void
    {
        $toggle = new StringContextToggle();

        self::assertSame('', $toggle->http);
        self::assertSame('', $toggle->cli);
        self::assertSame('', $toggle->test);
    }

    #[Test]
    public function constructorSetsCustomValues(): void
    {
        $toggle = new StringContextToggle(
            http: 'web-server',
            cli: 'command-line',
            test: 'test-environment',
        );

        self::assertSame('web-server', $toggle->http);
        self::assertSame('command-line', $toggle->cli);
        self::assertSame('test-environment', $toggle->test);
    }

    #[Test]
    #[DataProvider('contextValueProvider')]
    public function invokeReturnsCorrectValueForContext(Context $context, string $expected): void
    {
        $toggle = new StringContextToggle(
            http: 'web-server',
            cli: 'command-line',
            test: 'test-environment',
        );

        $result = $toggle($context);

        self::assertSame($expected, $result);
    }

    public static function contextValueProvider(): \Generator
    {
        yield 'HTTP context returns web-server' => [Context::Http, 'web-server'];
        yield 'CLI context returns command-line' => [Context::Cli, 'command-line'];
        yield 'Test context returns test-environment' => [Context::Test, 'test-environment'];
    }

    #[Test]
    public function handlesEmptyStrings(): void
    {
        $toggle = new StringContextToggle(
            http: '',
            cli: 'cli-value',
            test: '',
        );

        self::assertSame('', $toggle(Context::Http));
        self::assertSame('cli-value', $toggle(Context::Cli));
        self::assertSame('', $toggle(Context::Test));
    }

    #[Test]
    public function handlesSpecialCharacters(): void
    {
        $toggle = new StringContextToggle(
            http: 'special!@#$%^&*()_+-=[]{}|;:,.<>?',
            cli: 'unicode: 🚀🔥💯',
            test: "multiline\nstring\twith\ttabs",
        );

        self::assertSame('special!@#$%^&*()_+-=[]{}|;:,.<>?', $toggle(Context::Http));
        self::assertSame('unicode: 🚀🔥💯', $toggle(Context::Cli));
        self::assertSame("multiline\nstring\twith\ttabs", $toggle(Context::Test));
    }

    #[Test]
    public function handlesLongStrings(): void
    {
        $longString = \str_repeat('a', 10000);
        $toggle = new StringContextToggle(
            http: $longString,
            cli: 'short',
            test: $longString,
        );

        self::assertSame($longString, $toggle(Context::Http));
        self::assertSame('short', $toggle(Context::Cli));
        self::assertSame($longString, $toggle(Context::Test));
    }
}
