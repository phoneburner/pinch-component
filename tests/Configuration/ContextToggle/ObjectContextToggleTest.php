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
        $httpObject = new \stdClass();
        $cliObject = new \DateTimeImmutable();
        $testObject = new \RuntimeException();

        $toggle = new ObjectContextToggle(
            http: $httpObject,
            cli: $cliObject,
            test: $testObject,
        );

        self::assertSame($httpObject, $toggle->http);
        self::assertSame($cliObject, $toggle->cli);
        self::assertSame($testObject, $toggle->test);
    }

    #[Test]
    public function invokeReturnsCorrectValueForContext(): void
    {
        $httpObject = new \stdClass();
        $cliObject = new \DateTimeImmutable();
        $testObject = new \RuntimeException();

        $toggle = new ObjectContextToggle(
            http: $httpObject,
            cli: $cliObject,
            test: $testObject,
        );

        self::assertSame($httpObject, $toggle(Context::Http));
        self::assertSame($cliObject, $toggle(Context::Cli));
        self::assertSame($testObject, $toggle(Context::Test));
    }

    #[Test]
    public function handlesDifferentObjectTypes(): void
    {
        $closure = fn(): string => 'result';
        $dateTime = new \DateTimeImmutable('2024-01-01');
        $exception = new \InvalidArgumentException('test message');

        $toggle = new ObjectContextToggle(
            http: $closure,
            cli: $dateTime,
            test: $exception,
        );

        self::assertSame($closure, $toggle(Context::Http));
        self::assertSame($dateTime, $toggle(Context::Cli));
        self::assertSame($exception, $toggle(Context::Test));
    }

    #[Test]
    public function handlesAnonymousClasses(): void
    {
        $anonymousClass = new class {
            public string $property = 'test-value';

            public function method(): string
            {
                return 'method-result';
            }
        };

        $toggle = new ObjectContextToggle(
            http: $anonymousClass,
            cli: new \stdClass(),
            test: $anonymousClass,
        );

        self::assertSame($anonymousClass, $toggle(Context::Http));
        self::assertInstanceOf(\stdClass::class, $toggle(Context::Cli));
        self::assertSame($anonymousClass, $toggle(Context::Test));
    }

    #[Test]
    public function handlesObjectsWithState(): void
    {
        $splQueue = new \SplQueue();
        $splQueue->enqueue('item1');
        $splQueue->enqueue('item2');

        $arrayObject = new \ArrayObject(['key' => 'value']);

        $toggle = new ObjectContextToggle(
            http: $splQueue,
            cli: $arrayObject,
            test: new \stdClass(),
        );

        self::assertSame($splQueue, $toggle(Context::Http));
        self::assertSame($arrayObject, $toggle(Context::Cli));
        self::assertInstanceOf(\stdClass::class, $toggle(Context::Test));

        // Verify object state is preserved
        self::assertSame('item1', $splQueue->dequeue());
        self::assertSame('value', $arrayObject['key']);
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
