<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Event;

use PhoneBurner\Pinch\Component\Http\RateLimiter\RateLimitResult;

/**
 * Event emitted when a request is blocked due to rate limit exceeded
 *
 * This event is fired for the sad path when a request is denied
 * because the rate limits have been exceeded.
 */
final readonly class RequestRateLimitExceeded
{
    public function __construct(
        public RateLimitResult $result,
    ) {
    }
}
