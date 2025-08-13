<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\MessageBus\Handler;

use PhoneBurner\Pinch\Component\MessageBus\Event\InvokableMessageHandlingCompleted;
use PhoneBurner\Pinch\Component\MessageBus\Event\InvokableMessageHandlingFailed;
use PhoneBurner\Pinch\Component\MessageBus\Event\InvokableMessageHandlingStarted;
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
            $this->event_dispatcher->dispatch(new InvokableMessageHandlingStarted($message));
            $this->container->call($message);
            $this->event_dispatcher->dispatch(new InvokableMessageHandlingCompleted($message));
        } catch (\Throwable $e) {
            $this->event_dispatcher->dispatch(new InvokableMessageHandlingFailed($message, $e));
            throw $e;
        }
    }
}
