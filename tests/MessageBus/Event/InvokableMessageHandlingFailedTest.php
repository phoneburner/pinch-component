<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\MessageBus\Event;

use PhoneBurner\Pinch\Component\MessageBus\Event\InvokableMessageHandlingFailed;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

final class InvokableMessageHandlingFailedTest extends TestCase
{
    #[Test]
    public function constructorSetsMessageProperty(): void
    {
        $message = new stdClass();
        $event = new InvokableMessageHandlingFailed($message);

        self::assertSame($message, $event->message);
        self::assertNull($event->exception);
    }

    #[Test]
    public function constructorSetsExceptionProperty(): void
    {
        $message = new stdClass();
        $exception = new RuntimeException('Test exception');
        $event = new InvokableMessageHandlingFailed($message, $exception);

        self::assertSame($message, $event->message);
        self::assertSame($exception, $event->exception);
    }
}
