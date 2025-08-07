<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\MessageBus\Event;

class InvokableMessageHandlingComplete
{
    public function __construct(public readonly object $message)
    {
    }
}
