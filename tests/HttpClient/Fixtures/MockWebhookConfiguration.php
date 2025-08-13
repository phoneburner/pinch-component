<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\HttpClient\Fixtures;

use PhoneBurner\Pinch\Component\Http\Domain\HttpMethod;
use PhoneBurner\Pinch\Component\HttpClient\Webhook\Configuration\WebhookConfiguration;
use Psr\Http\Message\UriInterface;

/**
 * Mock webhook configuration for testing webhook delivery events
 */
final readonly class MockWebhookConfiguration implements WebhookConfiguration
{
    public function __construct(
        public array $events = ['user.created', 'user.updated'],
        public HttpMethod $method = HttpMethod::Post,
        public UriInterface|string $uri = 'https://example.com/webhook',
        public array $extra_headers = ['X-Custom-Header' => 'test-value'],
        public int $connect_timeout_seconds = self::DEFAULT_CONNECT_TIMEOUT_SECONDS,
        public int $request_timeout_seconds = self::DEFAULT_REQUEST_TIMEOUT_SECONDS,
        public int $max_retry_attempts = self::DEFAULT_MAX_RETRY_ATTEMPTS,
    ) {
    }

    public function toArray(): array
    {
        return [
            'events' => $this->events,
            'method' => $this->method->value,
            'uri' => (string)$this->uri,
            'extra_headers' => $this->extra_headers,
            'connect_timeout_seconds' => $this->connect_timeout_seconds,
            'request_timeout_seconds' => $this->request_timeout_seconds,
            'max_retry_attempts' => $this->max_retry_attempts,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            events: $data['events'] ?? ['user.created'],
            method: HttpMethod::from($data['method'] ?? 'POST'),
            uri: $data['uri'] ?? 'https://example.com/webhook',
            extra_headers: $data['extra_headers'] ?? [],
            connect_timeout_seconds: $data['connect_timeout_seconds'] ?? self::DEFAULT_CONNECT_TIMEOUT_SECONDS,
            request_timeout_seconds: $data['request_timeout_seconds'] ?? self::DEFAULT_REQUEST_TIMEOUT_SECONDS,
            max_retry_attempts: $data['max_retry_attempts'] ?? self::DEFAULT_MAX_RETRY_ATTEMPTS,
        );
    }
}
