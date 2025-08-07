<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\MessageBus\Event;

use PhoneBurner\Pinch\Component\MessageBus\Event\InvokableMessageHandlingComplete;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

final class InvokableMessageHandlingCompleteTest extends TestCase
{
    #[Test]
    public function constructorSetsMessageProperty(): void
    {
        $message = new stdClass();
        $event = new InvokableMessageHandlingComplete($message);

        self::assertSame($message, $event->message);
    }
}
