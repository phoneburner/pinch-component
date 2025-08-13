<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\HttpClient\Fixtures;

use PhoneBurner\Pinch\Component\HttpClient\Webhook\Configuration\WebhookConfiguration;
use PhoneBurner\Pinch\Component\HttpClient\Webhook\Message\WebhookDeliveryMessage;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Mock webhook delivery message for testing webhook delivery events
 */
final readonly class MockWebhookDeliveryMessage implements WebhookDeliveryMessage
{
    public readonly UuidInterface $webhook_id;

    public function __construct(
        public WebhookConfiguration $configuration = new MockWebhookConfiguration(),
        UuidInterface|null $webhook_id = null,
        public \DateTimeImmutable $timestamp = new \DateTimeImmutable('2024-01-15 12:00:00'),
        public \JsonSerializable|\Stringable|string|array $payload = ['event' => 'user.created', 'user_id' => 123],
        public int $attempt = 1,
    ) {
        $this->webhook_id = $webhook_id ?? Uuid::fromString('10554035-5bcb-4c0a-8f74-fcd745268359');
    }

    public function withNextAttempt(): self
    {
        return new self(
            configuration: $this->configuration,
            webhook_id: $this->webhook_id,
            timestamp: $this->timestamp,
            payload: $this->payload,
            attempt: $this->attempt + 1,
        );
    }

    public static function createWithAttempt(int $attempt): self
    {
        return new self(attempt: $attempt);
    }

    public static function createWithCustomData(
        WebhookConfiguration|null $configuration = null,
        UuidInterface|null $webhook_id = null,
        \DateTimeImmutable|null $timestamp = null,
        \JsonSerializable|\Stringable|string|array|null $payload = null,
        int|null $attempt = null,
    ): self {
        return new self(
            configuration: $configuration ?? new MockWebhookConfiguration(),
            webhook_id: $webhook_id ?? Uuid::fromString('10554035-5bcb-4c0a-8f74-fcd745268359'),
            timestamp: $timestamp ?? new \DateTimeImmutable('2024-01-15 12:00:00'),
            payload: $payload ?? ['event' => 'user.created', 'user_id' => 123],
            attempt: $attempt ?? 1,
        );
    }
}
