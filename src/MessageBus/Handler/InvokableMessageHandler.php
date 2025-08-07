<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\MessageBus\Handler;

use PhoneBurner\Pinch\Component\MessageBus\Event\InvokableMessageHandlingComplete;
use PhoneBurner\Pinch\Component\MessageBus\Event\InvokableMessageHandlingFailed;
use PhoneBurner\Pinch\Component\MessageBus\Event\InvokableMessageHandlingStarting;
use PhoneBurner\Pinch\Container\InvokingContainer;
use Psr\EventDispatcher\EventDispatcherInterface;

class InvokableMessageHandler
{
    public function __construct(
        private readonly InvokingContainer $container,
        private readonly EventDispatcherInterface $event_dispatcher,
    ) {
    }

    public function __invoke(object $message): void
    {
        try {
            $this->event_dispatcher->dispatch(new InvokableMessageHandlingStarting($message));
            $this->container->call($message);
            $this->event_dispatcher->dispatch(new InvokableMessageHandlingComplete($message));
        } catch (\Throwable $e) {
            $this->event_dispatcher->dispatch(new InvokableMessageHandlingFailed($message, $e));
            throw $e;
        }
    }
}
