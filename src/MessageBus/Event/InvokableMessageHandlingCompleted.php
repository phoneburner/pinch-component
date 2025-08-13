<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\MessageBus\Event;

use PhoneBurner\Pinch\Attribute\Psr14Event;

#[Psr14Event]
class InvokableMessageHandlingCompleted
{
    public function __construct(public readonly object $message)
    {
    }
}
