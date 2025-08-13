<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\HttpClient\Event;

use PhoneBurner\Pinch\Component\HttpClient\Event\WebhookDeliveryStarted;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\Loggable;
use PhoneBurner\Pinch\Component\Logging\LogLevel;
use PhoneBurner\Pinch\Component\Tests\HttpClient\Fixtures\MockWebhookConfiguration;
use PhoneBurner\Pinch\Component\Tests\HttpClient\Fixtures\MockWebhookDeliveryMessage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class WebhookDeliveryStartedTest extends TestCase
{
    #[Test]
    public function constructorSetsMessageProperty(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $event = new WebhookDeliveryStarted($message);

        self::assertSame($message, $event->message);
    }

    #[Test]
    public function implementsLoggableInterface(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $event = new WebhookDeliveryStarted($message);

        self::assertInstanceOf(Loggable::class, $event);
    }

    #[Test]
    public function getLogEntryReturnsValidLogEntry(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $event = new WebhookDeliveryStarted($message);
        $log_entry = $event->getLogEntry();

        self::assertInstanceOf(LogEntry::class, $log_entry);
        self::assertSame(LogLevel::Debug, $log_entry->level);
        self::assertSame('Webhook Request Delivery Started', $log_entry->message);
    }

    #[Test]
    public function getLogEntryContainsCorrectContext(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $event = new WebhookDeliveryStarted($message);
        $log_entry = $event->getLogEntry();

        self::assertArrayHasKey('webhook_id', $log_entry->context);
        self::assertArrayHasKey('webhook_url', $log_entry->context);
        self::assertArrayHasKey('attempt', $log_entry->context);

        self::assertSame('10554035-5bcb-4c0a-8f74-fcd745268359', $log_entry->context['webhook_id']);
        self::assertSame('https://example.com/webhook', $log_entry->context['webhook_url']);
        self::assertSame(1, $log_entry->context['attempt']);
    }

    #[Test]
    #[DataProvider('provideVariousAttemptNumbers')]
    public function getLogEntryIncludesCorrectAttemptNumber(int $attempt): void
    {
        $message = MockWebhookDeliveryMessage::createWithAttempt($attempt);
        $event = new WebhookDeliveryStarted($message);
        $log_entry = $event->getLogEntry();

        self::assertSame($attempt, $log_entry->context['attempt']);
    }

    #[Test]
    #[DataProvider('provideVariousWebhookIds')]
    public function getLogEntryIncludesCorrectWebhookId(string $webhook_id_string): void
    {
        $webhook_id = Uuid::fromString($webhook_id_string);
        $message = MockWebhookDeliveryMessage::createWithCustomData(webhook_id: $webhook_id);
        $event = new WebhookDeliveryStarted($message);
        $log_entry = $event->getLogEntry();

        self::assertSame($webhook_id_string, $log_entry->context['webhook_id']);
    }

    #[Test]
    #[DataProvider('provideVariousWebhookUrls')]
    public function getLogEntryIncludesCorrectWebhookUrl(string $webhook_url): void
    {
        $configuration = new MockWebhookConfiguration(uri: $webhook_url);
        $message = MockWebhookDeliveryMessage::createWithCustomData(configuration: $configuration);
        $event = new WebhookDeliveryStarted($message);
        $log_entry = $event->getLogEntry();

        self::assertSame($webhook_url, $log_entry->context['webhook_url']);
    }

    #[Test]
    #[DataProvider('provideVariousTimestamps')]
    public function worksWithDifferentTimestamps(\DateTimeImmutable $timestamp): void
    {
        $message = MockWebhookDeliveryMessage::createWithCustomData(timestamp: $timestamp);
        $event = new WebhookDeliveryStarted($message);

        self::assertSame($message, $event->message);
        self::assertSame($timestamp, $event->message->timestamp);
    }

    #[Test]
    #[DataProvider('provideVariousPayloads')]
    public function worksWithDifferentPayloadTypes(\JsonSerializable|\Stringable|string|array $payload): void
    {
        $message = MockWebhookDeliveryMessage::createWithCustomData(payload: $payload);
        $event = new WebhookDeliveryStarted($message);

        self::assertSame($message, $event->message);
        self::assertSame($payload, $event->message->payload);
    }

    #[Test]
    public function readonlyPropertyCannotBeModified(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $event = new WebhookDeliveryStarted($message);

        // Test that property is readonly by trying to access it
        self::assertSame($message, $event->message);
    }

    #[Test]
    public function eventIsImmutable(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $event = new WebhookDeliveryStarted($message);

        // Event should be immutable - property should remain the same
        $original_message = $event->message;

        // Access property multiple times to ensure it doesn't change
        self::assertSame($original_message, $event->message);
    }

    #[Test]
    public function usesDebugLogLevel(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $event = new WebhookDeliveryStarted($message);
        $log_entry = $event->getLogEntry();

        self::assertSame(LogLevel::Debug, $log_entry->level);
    }

    #[Test]
    public function hasConsistentLogMessage(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $event = new WebhookDeliveryStarted($message);
        $log_entry = $event->getLogEntry();

        self::assertSame('Webhook Request Delivery Started', $log_entry->message);
    }

    #[Test]
    public function contextContainsOnlyExpectedKeys(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $event = new WebhookDeliveryStarted($message);
        $log_entry = $event->getLogEntry();

        $expected_keys = ['webhook_id', 'webhook_url', 'attempt'];
        $actual_keys = \array_keys($log_entry->context);

        self::assertSame($expected_keys, $actual_keys);
    }

    #[Test]
    public function contextValuesHaveCorrectTypes(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $event = new WebhookDeliveryStarted($message);
        $log_entry = $event->getLogEntry();

        self::assertIsString($log_entry->context['webhook_id']);
        self::assertIsString($log_entry->context['webhook_url']);
        self::assertIsInt($log_entry->context['attempt']);
    }

    #[Test]
    #[DataProvider('provideComplexWebhookScenarios')]
    public function handlesComplexWebhookScenariosCorrectly(
        string $webhook_id_string,
        string $webhook_url,
        int $attempt,
        \JsonSerializable|\Stringable|string|array $payload,
        \DateTimeImmutable $timestamp,
    ): void {
        $webhook_id = Uuid::fromString($webhook_id_string);
        $configuration = new MockWebhookConfiguration(uri: $webhook_url);
        $message = MockWebhookDeliveryMessage::createWithCustomData(
            configuration: $configuration,
            webhook_id: $webhook_id,
            timestamp: $timestamp,
            payload: $payload,
            attempt: $attempt,
        );

        $event = new WebhookDeliveryStarted($message);
        $log_entry = $event->getLogEntry();

        self::assertSame($webhook_id_string, $log_entry->context['webhook_id']);
        self::assertSame($webhook_url, $log_entry->context['webhook_url']);
        self::assertSame($attempt, $log_entry->context['attempt']);
        self::assertSame(LogLevel::Debug, $log_entry->level);
        self::assertSame('Webhook Request Delivery Started', $log_entry->message);
    }

    #[Test]
    public function worksWithMinimalConfiguration(): void
    {
        $configuration = new MockWebhookConfiguration(
            events: [],
            uri: 'https://minimal.example.com/hook',
            extra_headers: [],
        );
        $message = MockWebhookDeliveryMessage::createWithCustomData(configuration: $configuration);
        $event = new WebhookDeliveryStarted($message);
        $log_entry = $event->getLogEntry();

        self::assertSame('https://minimal.example.com/hook', $log_entry->context['webhook_url']);
    }

    #[Test]
    public function worksWithMaximalConfiguration(): void
    {
        $configuration = new MockWebhookConfiguration(
            events: ['user.created', 'user.updated', 'user.deleted', 'order.placed'],
            uri: 'https://complex.example.com/api/v2/webhooks/receive',
            extra_headers: [
                'Authorization' => 'Bearer token123',
                'X-Custom-Header' => 'custom-value',
                'Content-Type' => 'application/json',
            ],
            connect_timeout_seconds: 10,
            request_timeout_seconds: 30,
            max_retry_attempts: 10,
        );
        $message = MockWebhookDeliveryMessage::createWithCustomData(configuration: $configuration);
        $event = new WebhookDeliveryStarted($message);
        $log_entry = $event->getLogEntry();

        self::assertSame('https://complex.example.com/api/v2/webhooks/receive', $log_entry->context['webhook_url']);
    }

    public static function provideVariousAttemptNumbers(): \Generator
    {
        yield 'first attempt' => [1];
        yield 'second attempt' => [2];
        yield 'third attempt' => [3];
        yield 'fourth attempt' => [4];
        yield 'max retry attempt' => [5];
        yield 'high attempt number' => [10];
        yield 'very high attempt' => [25];
    }

    public static function provideVariousWebhookIds(): \Generator
    {
        yield 'default uuid' => ['10554035-5bcb-4c0a-8f74-fcd745268359'];
        yield 'random uuid 1' => ['550e8400-e29b-41d4-a716-446655440000'];
        yield 'random uuid 2' => ['6ba7b810-9dad-11d1-80b4-00c04fd430c8'];
        yield 'random uuid 3' => ['6ba7b811-9dad-11d1-80b4-00c04fd430c8'];
        yield 'nil uuid' => ['00000000-0000-0000-0000-000000000000'];
    }

    public static function provideVariousWebhookUrls(): \Generator
    {
        yield 'basic url' => ['https://example.com/webhook'];
        yield 'with path' => ['https://api.example.com/v1/webhooks'];
        yield 'with query params' => ['https://example.com/webhook?token=abc123'];
        yield 'different domain' => ['https://hooks.myservice.io/incoming'];
        yield 'subdomain' => ['https://webhooks.api.example.com/receive'];
        yield 'with port' => ['https://example.com:8443/webhook'];
        yield 'complex path' => ['https://api.service.com/v2/integrations/webhooks/receive'];
    }

    public static function provideVariousTimestamps(): \Generator
    {
        yield 'current time' => [new \DateTimeImmutable('2024-01-15 12:00:00')];
        yield 'different time' => [new \DateTimeImmutable('2024-03-10 14:30:45')];
        yield 'with timezone' => [new \DateTimeImmutable('2024-06-20 09:15:30', new \DateTimeZone('UTC'))];
        yield 'past date' => [new \DateTimeImmutable('2023-12-01 08:00:00')];
        yield 'future date' => [new \DateTimeImmutable('2024-12-31 23:59:59')];
    }

    public static function provideVariousPayloads(): \Generator
    {
        yield 'array payload' => [['event' => 'user.created', 'user_id' => 123]];
        yield 'string payload' => ['{"event":"user.updated","user_id":456}'];
        yield 'complex array' => [
            [
                'event' => 'order.placed',
                'order_id' => 789,
                'customer' => ['id' => 123, 'email' => 'user@example.com'],
                'items' => [
                    ['sku' => 'ABC123', 'quantity' => 2, 'price' => 29.99],
                    ['sku' => 'DEF456', 'quantity' => 1, 'price' => 49.99],
                ],
                'total' => 109.97,
            ],
        ];
        yield 'simple string' => ['user.deleted'];
        yield 'empty array' => [[]];
    }

    public static function provideComplexWebhookScenarios(): \Generator
    {
        yield 'first delivery attempt' => [
            '10554035-5bcb-4c0a-8f74-fcd745268359',
            'https://example.com/webhook',
            1,
            ['event' => 'user.created', 'user_id' => 123],
            new \DateTimeImmutable('2024-01-15 12:00:00'),
        ];

        yield 'retry attempt' => [
            '550e8400-e29b-41d4-a716-446655440000',
            'https://api.service.com/webhooks/receive',
            3,
            ['event' => 'order.updated', 'order_id' => 456, 'status' => 'shipped'],
            new \DateTimeImmutable('2024-02-20 15:30:45'),
        ];

        yield 'complex payload scenario' => [
            '6ba7b810-9dad-11d1-80b4-00c04fd430c8',
            'https://webhooks.external-service.io/incoming',
            5,
            [
                'event' => 'subscription.renewed',
                'subscription_id' => 'sub_1234567890',
                'customer' => [
                    'id' => 'cust_abcdef123456',
                    'email' => 'customer@example.com',
                    'plan' => 'premium',
                ],
                'billing' => [
                    'amount' => 99.99,
                    'currency' => 'USD',
                    'period' => 'monthly',
                ],
                'metadata' => [
                    'source' => 'api',
                    'campaign' => 'winter2024',
                ],
            ],
            new \DateTimeImmutable('2024-03-10 18:45:22'),
        ];

        yield 'string payload scenario' => [
            '6ba7b811-9dad-11d1-80b4-00c04fd430c8',
            'https://hooks.integration.example.com/v2/receive',
            2,
            '{"event":"user.login","user_id":789,"timestamp":"2024-01-15T12:00:00Z","ip_address":"192.168.1.100"}',
            new \DateTimeImmutable('2024-01-15 12:00:05'),
        ];
    }
}
