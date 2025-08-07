<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\RateLimiter;

use PhoneBurner\Pinch\Component\Http\Domain\RateLimits;
use PhoneBurner\Pinch\Component\Http\Event\RequestRateLimitUpdated;
use PhoneBurner\Pinch\Component\Http\RateLimiter\RateLimiter;
use PhoneBurner\Pinch\Component\Http\RateLimiter\RateLimitResult;
use Psr\Clock\ClockInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

final readonly class NullRateLimiter implements RateLimiter
{
    public function __construct(
        private ClockInterface $clock,
        private EventDispatcherInterface $event_dispatcher,
    ) {
    }

    public function throttle(RateLimits $limits): RateLimitResult
    {
        $now = $this->clock->now();
        $reset_time = $now->setTime((int)$now->format('H'), (int)$now->format('i') + 1, 0);

        $result = RateLimitResult::allowed(
            remaining_per_second: $limits->per_second,
            remaining_per_minute: $limits->per_minute,
            reset_time: $reset_time,
            rate_limits: $limits,
        );

        $this->event_dispatcher->dispatch(new RequestRateLimitUpdated($result));

        return $result;
    }
}
