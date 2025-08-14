<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\RateLimiter;

use PhoneBurner\Pinch\Component\Http\Domain\RateLimits;
use PhoneBurner\Pinch\Component\Http\Event\RequestRateLimitUpdated;
use PhoneBurner\Pinch\Time\Clock\Clock;
use Psr\EventDispatcher\EventDispatcherInterface;

final readonly class NullRateLimiter implements RateLimiter
{
    public function __construct(
        private Clock $clock,
        private EventDispatcherInterface $event_dispatcher,
    ) {
    }

    public function throttle(RateLimits $limits): RateLimitResult
    {
        $result = RateLimitResult::allowed(
            remaining_per_second: $limits->per_second,
            remaining_per_minute: $limits->per_minute,
            reset_time: $this->clock->now()->addMinutes(1),
            rate_limits: $limits,
        );

        $this->event_dispatcher->dispatch(new RequestRateLimitUpdated($result));

        return $result;
    }
}
