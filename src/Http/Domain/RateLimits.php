<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Domain;

use PhoneBurner\Pinch\Component\Http\Exception\InvalidRateLimits;

/**
 * Value object representing rate limiting configuration
 *
 * Defines per-second and per-minute limits for HTTP requests with validation.
 * Used with ThrottleRequests middleware to control request rates per identifier.
 */
final readonly class RateLimits
{
    /**
     * @param string $id Non-empty string identifier for the rate limit group
     * @param int $per_second Maximum requests allowed per second (must be positive)
     * @param int $per_minute Maximum requests allowed per minute (must be positive)
     * @throws InvalidRateLimits When validation fails for any parameter
     */
    public function __construct(
        public string $id,
        public int $per_second = 10,
        public int $per_minute = 60,
    ) {
        if ($this->id === '') {
            throw new InvalidRateLimits('Rate limit ID cannot be empty');
        }

        if ($this->per_second <= 0) {
            throw new InvalidRateLimits('Per-second limit must be positive, got: ' . $this->per_second);
        }

        if ($this->per_minute <= 0) {
            throw new InvalidRateLimits('Per-minute limit must be positive, got: ' . $this->per_minute);
        }

        // Validate that per-minute is at least per-second
        if ($this->per_minute < $this->per_second) {
            throw new InvalidRateLimits(
                'Per-minute limit (' . $this->per_minute . ') cannot be less than per-second limit (' . $this->per_second . ')',
            );
        }
    }
}
