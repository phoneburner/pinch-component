<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\HttpClient\Event;

use PhoneBurner\Pinch\Attribute\Psr14Event;
use PhoneBurner\Pinch\Component\Http\RequestAware;
use PhoneBurner\Pinch\Component\Http\ResponseAware;
use PhoneBurner\Pinch\Component\HttpClient\Webhook\Message\WebhookDeliveryMessage;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\Loggable;
use PhoneBurner\Pinch\Component\Logging\LogLevel;
use PhoneBurner\Pinch\Time\Timer\ElapsedTime;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

#[Psr14Event]
final readonly class WebhookDeliveryCompleted implements Loggable, RequestAware, ResponseAware
{
    public function __construct(
        public WebhookDeliveryMessage $message,
        public RequestInterface $request,
        public ResponseInterface $response,
        public ElapsedTime $elapsed_time,
    ) {
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(
            level: LogLevel::Info,
            message: 'Webhook Request Delivery Completed',
            context: [
                'webhook_id' => $this->message->webhook_id->toString(),
                'webhook_url' => $this->message->configuration->uri,
                'attempt' => $this->message->attempt,
                'elapsed_microtime' => $this->elapsed_time->inMicroseconds(),
            ],
        );
    }
}
