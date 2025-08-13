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
        $http_object = new \stdClass();
        $test_object = new \RuntimeException();

        $toggle = new NullableObjectContextToggle(
            http: $http_object,
            cli: null,
            test: $test_object,
        );

        self::assertSame($http_object, $toggle->http);
        self::assertNull($toggle->cli);
        self::assertSame($test_object, $toggle->test);
    }

    #[Test]
    public function invokeReturnsCorrectValueForContext(): void
    {
        $http_object = new \stdClass();
        $test_object = new \RuntimeException();

        $toggle = new NullableObjectContextToggle(
            http: $http_object,
            cli: null,
            test: $test_object,
        );

        self::assertSame($http_object, $toggle(Context::Http));
        self::assertNull($toggle(Context::Cli));
        self::assertSame($test_object, $toggle(Context::Test));
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
        $datetime = new \DateTimeImmutable('2024-01-01');

        $toggle = new NullableObjectContextToggle(
            http: $closure,
            cli: null,
            test: $datetime,
        );

        self::assertSame($closure, $toggle(Context::Http));
        self::assertNull($toggle(Context::Cli));
        self::assertSame($datetime, $toggle(Context::Test));
    }

    #[Test]
    public function handlesAnonymousClassesAndNull(): void
    {
        $anonymous_class = new class {
            public string $property = 'test-value';

            public function method(): string
            {
                return 'method-result';
            }
        };

        $toggle = new NullableObjectContextToggle(
            http: null,
            cli: $anonymous_class,
            test: null,
        );

        self::assertNull($toggle(Context::Http));
        self::assertSame($anonymous_class, $toggle(Context::Cli));
        self::assertNull($toggle(Context::Test));
    }

    #[Test]
    public function handlesObjectsWithStateAndNull(): void
    {
        $spl_queue = new \SplQueue();
        $spl_queue->enqueue('item1');
        $spl_queue->enqueue('item2');

        $toggle = new NullableObjectContextToggle(
            http: $spl_queue,
            cli: null,
            test: new \stdClass(),
        );

        self::assertSame($spl_queue, $toggle(Context::Http));
        self::assertNull($toggle(Context::Cli));
        self::assertInstanceOf(\stdClass::class, $toggle(Context::Test));

        // Verify object state is preserved
        self::assertSame('item1', $spl_queue->dequeue());
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
