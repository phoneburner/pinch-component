<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\ContextToggle\MixedContextToggle;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MixedContextToggleTest extends TestCase
{
    #[Test]
    public function constructorSetsDefaultValues(): void
    {
        $toggle = new MixedContextToggle();

        self::assertNull($toggle->http);
        self::assertNull($toggle->cli);
        self::assertNull($toggle->test);
    }

    #[Test]
    public function constructorSetsCustomValues(): void
    {
        $object = new \stdClass();

        $toggle = new MixedContextToggle(
            http: 'string-value',
            cli: 42,
            test: $object,
        );

        self::assertSame('string-value', $toggle->http);
        self::assertSame(42, $toggle->cli);
        self::assertSame($object, $toggle->test);
    }

    #[Test]
    public function invokeReturnsCorrectValueForContext(): void
    {
        $object = new \stdClass();

        $toggle = new MixedContextToggle(
            http: 'string-value',
            cli: 42,
            test: $object,
        );

        self::assertSame('string-value', $toggle(Context::Http));
        self::assertSame(42, $toggle(Context::Cli));
        self::assertSame($object, $toggle(Context::Test));
    }

    #[Test]
    public function handlesDifferentTypes(): void
    {
        $array = ['key' => 'value'];
        $resource = \fopen('php://memory', 'r') ?: throw new \RuntimeException('Unable to open resource');

        $toggle = new MixedContextToggle(
            http: true,
            cli: $array,
            test: $resource,
        );

        self::assertTrue($toggle(Context::Http));
        self::assertSame($array, $toggle(Context::Cli));
        self::assertSame($resource, $toggle(Context::Test));

        \fclose($resource);
    }

    #[Test]
    public function handlesNull(): void
    {
        $toggle = new MixedContextToggle(
            http: null,
            cli: 'not-null',
            test: null,
        );

        self::assertNull($toggle(Context::Http));
        self::assertSame('not-null', $toggle(Context::Cli));
        self::assertNull($toggle(Context::Test));
    }

    #[Test]
    public function handlesCallables(): void
    {
        $closure = fn(): string => 'closure-result';
        $callback = 'strlen';

        $toggle = new MixedContextToggle(
            http: $closure,
            cli: $callback,
            test: $this->handlesCallables(...),
        );

        self::assertSame($closure, $toggle(Context::Http));
        self::assertSame($callback, $toggle(Context::Cli));
        self::assertEquals($this->handlesCallables(...), $toggle(Context::Test));
    }

    #[Test]
    public function handlesComplexObjects(): void
    {
        $dateTime = new \DateTimeImmutable();
        $exception = new \RuntimeException('test');

        $toggle = new MixedContextToggle(
            http: $dateTime,
            cli: $exception,
            test: Context::Http,
        );

        self::assertSame($dateTime, $toggle(Context::Http));
        self::assertSame($exception, $toggle(Context::Cli));
        self::assertSame(Context::Http, $toggle(Context::Test));
    }
}
