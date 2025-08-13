<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\HttpClient\Webhook\Configuration;

use PhoneBurner\Pinch\Component\Http\Domain\HttpMethod;
use Psr\Http\Message\UriInterface;

interface WebhookConfiguration
{
    public const int DEFAULT_CONNECT_TIMEOUT_SECONDS = 5;

    public const int DEFAULT_REQUEST_TIMEOUT_SECONDS = 10;

    public const int DEFAULT_MAX_RETRY_ATTEMPTS = 5;

    // phpcs:ignore
    public array $events { get; }

    // phpcs:ignore
    public HttpMethod $method { get; }

    // phpcs:ignore
    public UriInterface|string $uri { get; }

    // phpcs:ignore
    public array $extra_headers { get; }

    // phpcs:ignore
    public int $connect_timeout_seconds { get; }

    // phpcs:ignore
    public int $request_timeout_seconds { get; }

    // phpcs:ignore
    public int $max_retry_attempts { get; }

    public function toArray(): array;

    public static function fromArray(array $data): self;
}
