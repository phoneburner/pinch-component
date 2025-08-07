<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\MessageBus\Event;

use PhoneBurner\Pinch\Component\MessageBus\Event\InvokableMessageHandlingStarting;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

final class InvokableMessageHandlingStartingTest extends TestCase
{
    #[Test]
    public function constructorSetsMessageProperty(): void
    {
        $message = new stdClass();
        $event = new InvokableMessageHandlingStarting($message);

        self::assertSame($message, $event->message);
    }
}
