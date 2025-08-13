<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Event;

use PhoneBurner\Pinch\Attribute\Psr14Event;
use PhoneBurner\Pinch\Component\Http\RateLimiter\RateLimitResult;

/**
 * Event emitted when a request is allowed and rate limit counters are updated
 *
 * This event is fired for the happy path when a request passes rate limiting
 * and the internal counters are updated to reflect the consumed request.
 */
#[Psr14Event]
final readonly class RequestRateLimitUpdated
{
    public function __construct(
        public RateLimitResult $result,
    ) {
    }
}
