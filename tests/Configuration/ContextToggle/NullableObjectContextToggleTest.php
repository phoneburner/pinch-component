<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\ContextToggle\NullableObjectContextToggle;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NullableObjectContextToggleTest extends TestCase
{
    #[Test]
    public function constructorSetsDefaultValues(): void
    {
        $toggle = new NullableObjectContextToggle();

        self::assertNull($toggle->http);
        self::assertNull($toggle->cli);
        self::assertNull($toggle->test);
    }

    #[Test]
    public function constructorSetsCustomValues(): void
    {
        $httpObject = new \stdClass();
        $testObject = new \RuntimeException();

        $toggle = new NullableObjectContextToggle(
            http: $httpObject,
            cli: null,
            test: $testObject,
        );

        self::assertSame($httpObject, $toggle->http);
        self::assertNull($toggle->cli);
        self::assertSame($testObject, $toggle->test);
    }

    #[Test]
    public function invokeReturnsCorrectValueForContext(): void
    {
        $httpObject = new \stdClass();
        $testObject = new \RuntimeException();

        $toggle = new NullableObjectContextToggle(
            http: $httpObject,
            cli: null,
            test: $testObject,
        );

        self::assertSame($httpObject, $toggle(Context::Http));
        self::assertNull($toggle(Context::Cli));
        self::assertSame($testObject, $toggle(Context::Test));
    }

    #[Test]
    public function handlesAllNullValues(): void
    {
        $toggle = new NullableObjectContextToggle(
            http: null,
            cli: null,
            test: null,
        );

        self::assertNull($toggle(Context::Http));
        self::assertNull($toggle(Context::Cli));
        self::assertNull($toggle(Context::Test));
    }

    #[Test]
    public function handlesDifferentObjectTypesAndNull(): void
    {
        $closure = fn(): string => 'result';
        $dateTime = new \DateTimeImmutable('2024-01-01');

        $toggle = new NullableObjectContextToggle(
            http: $closure,
            cli: null,
            test: $dateTime,
        );

        self::assertSame($closure, $toggle(Context::Http));
        self::assertNull($toggle(Context::Cli));
        self::assertSame($dateTime, $toggle(Context::Test));
    }

    #[Test]
    public function handlesAnonymousClassesAndNull(): void
    {
        $anonymousClass = new class {
            public string $property = 'test-value';

            public function method(): string
            {
                return 'method-result';
            }
        };

        $toggle = new NullableObjectContextToggle(
            http: null,
            cli: $anonymousClass,
            test: null,
        );

        self::assertNull($toggle(Context::Http));
        self::assertSame($anonymousClass, $toggle(Context::Cli));
        self::assertNull($toggle(Context::Test));
    }

    #[Test]
    public function handlesObjectsWithStateAndNull(): void
    {
        $splQueue = new \SplQueue();
        $splQueue->enqueue('item1');
        $splQueue->enqueue('item2');

        $toggle = new NullableObjectContextToggle(
            http: $splQueue,
            cli: null,
            test: new \stdClass(),
        );

        self::assertSame($splQueue, $toggle(Context::Http));
        self::assertNull($toggle(Context::Cli));
        self::assertInstanceOf(\stdClass::class, $toggle(Context::Test));

        // Verify object state is preserved
        self::assertSame('item1', $splQueue->dequeue());
    }

    #[Test]
    public function handlesEnumsAndNull(): void
    {
        $context = Context::Http;

        $toggle = new NullableObjectContextToggle(
            http: null,
            cli: $context,
            test: null,
        );

        self::assertNull($toggle(Context::Http));
        self::assertSame($context, $toggle(Context::Cli));
        self::assertNull($toggle(Context::Test));

        // Verify enum functionality
        self::assertInstanceOf(\UnitEnum::class, $toggle(Context::Cli));
    }
}
