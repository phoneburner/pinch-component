<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\HttpClient\Webhook\Message;

use PhoneBurner\Pinch\Component\HttpClient\Webhook\Configuration\WebhookConfiguration;
use Ramsey\Uuid\UuidInterface;

interface WebhookDeliveryMessage
{
    // phpcs:ignore
    public WebhookConfiguration $configuration { get; }

    // phpcs:ignore
    public UuidInterface $webhook_id { get; }

    // phpcs:ignore
    public \DateTimeImmutable $timestamp { get; }

    // phpcs:ignore
    public \JsonSerializable|\Stringable|string|array $payload { get; }

    // phpcs:ignore
    public int $attempt { get; }

    /**
     * Method should return a new instance of the message with the next attempt
     * number incremented by one.
     */
    public function withNextAttempt(): self;
}
