<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\RateLimiter;

use PhoneBurner\Pinch\Component\Http\Domain\RateLimits;
use PhoneBurner\Pinch\Component\Http\Event\RequestRateLimitUpdated;
use PhoneBurner\Pinch\Component\Http\RateLimiter\NullRateLimiter;
use PhoneBurner\Pinch\Time\Clock\StaticClock;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

final class NullRateLimiterTest extends TestCase
{
    private EventDispatcherInterface&MockObject $event_dispatcher;
    private NullRateLimiter $limiter;

    protected function setUp(): void
    {
        $clock = new StaticClock(new \DateTimeImmutable('2022-01-20 14:30:00'));
        $this->event_dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->limiter = new NullRateLimiter($clock, $this->event_dispatcher);
    }

    #[Test]
    public function throttleAlwaysAllowsRequests(): void
    {
        $this->event_dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(RequestRateLimitUpdated::class));

        $limits = new RateLimits(id: 'test', per_second: 5, per_minute: 100);

        $result = $this->limiter->throttle($limits);

        self::assertTrue($result->allowed);
        self::assertSame(5, $result->remaining_per_second);
        self::assertSame(100, $result->remaining_per_minute);
        self::assertSame($limits, $result->rate_limits);
    }

    #[Test]
    public function throttleWorksWithDefaultLimits(): void
    {
        $this->event_dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(RequestRateLimitUpdated::class));

        $limits = new RateLimits(id: 'test');

        $result = $this->limiter->throttle($limits);

        self::assertTrue($result->allowed);
        self::assertSame(10, $result->remaining_per_second);
        self::assertSame(60, $result->remaining_per_minute);
        self::assertSame($limits, $result->rate_limits);
    }

    #[Test]
    public function throttleSetsCorrectResetTime(): void
    {
        $this->event_dispatcher->expects($this->once())
            ->method('dispatch');

        $limits = new RateLimits(id: 'test');

        $result = $this->limiter->throttle($limits);

        $expected_reset = new \DateTimeImmutable('2022-01-20 14:31:00');
        self::assertEquals($expected_reset, $result->reset_time);
    }
}
