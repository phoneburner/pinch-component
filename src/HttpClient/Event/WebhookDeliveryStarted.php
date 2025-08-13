<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\HttpClient\Event;

use PhoneBurner\Pinch\Attribute\Psr14Event;
use PhoneBurner\Pinch\Component\HttpClient\Webhook\Message\WebhookDeliveryMessage;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\Loggable;
use PhoneBurner\Pinch\Component\Logging\LogLevel;

#[Psr14Event]
final readonly class WebhookDeliveryStarted implements Loggable
{
    public function __construct(public WebhookDeliveryMessage $message)
    {
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(
            level: LogLevel::Debug,
            message: 'Webhook Request Delivery Started',
            context: [
                'webhook_id' => $this->message->webhook_id->toString(),
                'webhook_url' => $this->message->configuration->uri,
                'attempt' => $this->message->attempt,
            ],
        );
    }
}
