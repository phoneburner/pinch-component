<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\RateLimiter;

use PhoneBurner\Pinch\Component\Http\Domain\RateLimits;
use PhoneBurner\Pinch\Component\Http\RateLimiter\RateLimitResult;

/**
 * Interface for rate limiting implementations
 *
 * Implementations should track requests per identifier and enforce
 * per-second and per-minute limits using appropriate storage mechanisms.
 */
interface RateLimiter
{
    /**
     * Check if a request is allowed under the given rate limits
     *
     * @param RateLimits $limits The rate limits to enforce
     * @return RateLimitResult Contains whether the request is allowed and remaining limits
     */
    public function throttle(RateLimits $limits): RateLimitResult;
}
