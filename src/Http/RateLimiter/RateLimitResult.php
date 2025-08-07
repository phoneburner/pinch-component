<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\RateLimiter;

use DateTimeInterface;
use PhoneBurner\Pinch\Component\Http\Domain\RateLimits;

/**
 * Result of a rate limit check
 *
 * Contains information about whether the request is allowed,
 * remaining limits, and reset times for HTTP headers.
 */
final readonly class RateLimitResult
{
    public function __construct(
        public bool $allowed,
        public int $remaining_per_second,
        public int $remaining_per_minute,
        public DateTimeInterface $reset_time,
        public RateLimits $rate_limits,
    ) {
    }

    /**
     * Create result for allowed request
     */
    public static function allowed(
        int $remaining_per_second,
        int $remaining_per_minute,
        DateTimeInterface $reset_time,
        RateLimits $rate_limits,
    ): self {
        return new self(
            allowed: true,
            remaining_per_second: $remaining_per_second,
            remaining_per_minute: $remaining_per_minute,
            reset_time: $reset_time,
            rate_limits: $rate_limits,
        );
    }

    /**
     * Create result for blocked request
     */
    public static function blocked(
        DateTimeInterface $reset_time,
        RateLimits $rate_limits,
    ): self {
        return new self(
            allowed: false,
            remaining_per_second: 0,
            remaining_per_minute: 0,
            reset_time: $reset_time,
            rate_limits: $rate_limits,
        );
    }

    /**
     * Get retry-after seconds for blocked requests
     */
    public function getRetryAfterSeconds(): int
    {
        if ($this->allowed) {
            return 0;
        }

        $now = new \DateTimeImmutable();
        $diff = $this->reset_time->getTimestamp() - $now->getTimestamp();

        return \max(1, $diff);
    }
}
