<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\MessageBus\Event;

use PhoneBurner\Pinch\Attribute\Psr14Event;

#[Psr14Event]
class InvokableMessageHandlingFailed
{
    public function __construct(
        public readonly object $message,
        public readonly \Throwable|null $exception = null,
    ) {
    }
}
