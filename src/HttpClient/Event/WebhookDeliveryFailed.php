<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\HttpClient\Event;

use PhoneBurner\Pinch\Attribute\Psr14Event;
use PhoneBurner\Pinch\Component\Http\NullableRequestAware;
use PhoneBurner\Pinch\Component\Http\NullableResponseAware;
use PhoneBurner\Pinch\Component\HttpClient\Webhook\Message\WebhookDeliveryMessage;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\Loggable;
use PhoneBurner\Pinch\Component\Logging\LogLevel;
use PhoneBurner\Pinch\Time\Timer\ElapsedTime;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

#[Psr14Event]
final readonly class WebhookDeliveryFailed implements Loggable, NullableRequestAware, NullableResponseAware
{
    public function __construct(
        public WebhookDeliveryMessage $message,
        public RequestInterface|null $request = null,
        public ResponseInterface|null $response = null,
        public ElapsedTime|null $elapsed_time = null,
        public bool $retryable = true,
        public \Throwable|null $exception = null,
    ) {
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(
            level: $this->exception ? LogLevel::Critical : LogLevel::Error,
            message: \sprintf('Webhook Request Delivery %s Failure', $this->retryable ? 'Retryable' : 'Permanent'),
            context: [
                'webhook_id' => $this->message->webhook_id->toString(),
                'webhook_url' => $this->message->configuration->uri,
                'attempt' => $this->message->attempt,
                'status_code' => $this->response?->getStatusCode(),
            ],
        );
    }
}
