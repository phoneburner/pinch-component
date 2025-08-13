<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\ContextToggle\ObjectContextToggle;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ObjectContextToggleTest extends TestCase
{
    #[Test]
    public function constructorSetsCustomValues(): void
    {
        $http_object = new \stdClass();
        $cli_object = new \DateTimeImmutable();
        $test_object = new \RuntimeException();

        $toggle = new ObjectContextToggle(
            http: $http_object,
            cli: $cli_object,
            test: $test_object,
        );

        self::assertSame($http_object, $toggle->http);
        self::assertSame($cli_object, $toggle->cli);
        self::assertSame($test_object, $toggle->test);
    }

    #[Test]
    public function invokeReturnsCorrectValueForContext(): void
    {
        $http_object = new \stdClass();
        $cli_object = new \DateTimeImmutable();
        $test_object = new \RuntimeException();

        $toggle = new ObjectContextToggle(
            http: $http_object,
            cli: $cli_object,
            test: $test_object,
        );

        self::assertSame($http_object, $toggle(Context::Http));
        self::assertSame($cli_object, $toggle(Context::Cli));
        self::assertSame($test_object, $toggle(Context::Test));
    }

    #[Test]
    public function handlesDifferentObjectTypes(): void
    {
        $closure = fn(): string => 'result';
        $datetime = new \DateTimeImmutable('2024-01-01');
        $exception = new \InvalidArgumentException('test message');

        $toggle = new ObjectContextToggle(
            http: $closure,
            cli: $datetime,
            test: $exception,
        );

        self::assertSame($closure, $toggle(Context::Http));
        self::assertSame($datetime, $toggle(Context::Cli));
        self::assertSame($exception, $toggle(Context::Test));
    }

    #[Test]
    public function handlesAnonymousClasses(): void
    {
        $anonymous_class = new class {
            public string $property = 'test-value';

            public function method(): string
            {
                return 'method-result';
            }
        };

        $toggle = new ObjectContextToggle(
            http: $anonymous_class,
            cli: new \stdClass(),
            test: $anonymous_class,
        );

        self::assertSame($anonymous_class, $toggle(Context::Http));
        self::assertInstanceOf(\stdClass::class, $toggle(Context::Cli));
        self::assertSame($anonymous_class, $toggle(Context::Test));
    }

    #[Test]
    public function handlesObjectsWithState(): void
    {
        $spl_queue = new \SplQueue();
        $spl_queue->enqueue('item1');
        $spl_queue->enqueue('item2');

        $array_object = new \ArrayObject(['key' => 'value']);

        $toggle = new ObjectContextToggle(
            http: $spl_queue,
            cli: $array_object,
            test: new \stdClass(),
        );

        self::assertSame($spl_queue, $toggle(Context::Http));
        self::assertSame($array_object, $toggle(Context::Cli));
        self::assertInstanceOf(\stdClass::class, $toggle(Context::Test));

        // Verify object state is preserved
        self::assertSame('item1', $spl_queue->dequeue());
        self::assertSame('value', $array_object['key']);
    }

    #[Test]
    public function handlesObjectsImplementingInterfaces(): void
    {
        $context = Context::Http;
        $iterator = new \ArrayIterator([1, 2, 3]);
        $countable = new \ArrayObject([1, 2, 3, 4]);

        $toggle = new ObjectContextToggle(
            http: $context,
            cli: $iterator,
            test: $countable,
        );

        self::assertSame($context, $toggle(Context::Http));
        self::assertSame($iterator, $toggle(Context::Cli));
        self::assertSame($countable, $toggle(Context::Test));

        // Verify interface functionality
        self::assertInstanceOf(\UnitEnum::class, $toggle(Context::Http));
        self::assertInstanceOf(\Iterator::class, $toggle(Context::Cli));
        self::assertInstanceOf(\Countable::class, $toggle(Context::Test));
    }
}
