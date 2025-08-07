<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Middleware;

use DateTimeImmutable;
use PhoneBurner\Pinch\Component\Http\Domain\HttpHeader;
use PhoneBurner\Pinch\Component\Http\Domain\RateLimits;
use PhoneBurner\Pinch\Component\Http\Middleware\ThrottleRequests;
use PhoneBurner\Pinch\Component\Http\RateLimiter\RateLimiter;
use PhoneBurner\Pinch\Component\Http\RateLimiter\RateLimitResult;
use PhoneBurner\Pinch\Component\Http\Response\Exceptional\TooManyRequestsResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ThrottleRequestsTest extends TestCase
{
    private RateLimiter&MockObject $rate_limiter;
    private ServerRequestInterface&MockObject $request;
    private RequestHandlerInterface&MockObject $handler;
    private ResponseInterface&MockObject $response;

    protected function setUp(): void
    {
        $this->rate_limiter = $this->createMock(RateLimiter::class);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->handler = $this->createMock(RequestHandlerInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
    }

    #[Test]
    public function processUsesExistingRateLimitsFromRequest(): void
    {
        $rate_limits = new RateLimits(id: 'custom-limits', per_second: 5, per_minute: 100);

        $this->request->expects($this->once())
            ->method('getAttribute')
            ->with(RateLimits::class)
            ->willReturn($rate_limits);

        $result = RateLimitResult::allowed(
            remaining_per_second: 4,
            remaining_per_minute: 99,
            reset_time: new DateTimeImmutable('+1 minute'),
            rate_limits: $rate_limits,
        );

        $this->rate_limiter->expects($this->once())
            ->method('throttle')
            ->with($rate_limits)
            ->willReturn($result);

        $this->handler->expects($this->once())
            ->method('handle')
            ->with($this->request)
            ->willReturn($this->response);

        $this->response->expects($this->exactly(2))
            ->method('withHeader')
            ->willReturnSelf();

        $middleware = new ThrottleRequests($this->rate_limiter);
        $middleware->process($this->request, $this->handler);
    }

    #[Test]
    public function processCreatesDefaultRateLimitsWhenNotProvided(): void
    {
        $this->request->expects($this->exactly(2))
            ->method('getAttribute')
            ->willReturnCallback(function ($key): string|null {
                if ($key === RateLimits::class) {
                    return null;
                }
                if ($key === 'ip_address') {
                    return '192.168.1.1';
                }
                return null;
            });

        $result = RateLimitResult::allowed(
            remaining_per_second: 9,
            remaining_per_minute: 59,
            reset_time: new DateTimeImmutable('+1 minute'),
            rate_limits: new RateLimits(id: 'ip:192.168.1.1', per_second: 10, per_minute: 60),
        );

        $this->rate_limiter->expects($this->once())
            ->method('throttle')
            ->with($this->callback(function (RateLimits $limits): bool {
                return $limits->id === 'ip:192.168.1.1'
                    && $limits->per_second === 10
                    && $limits->per_minute === 60;
            }))
            ->willReturn($result);

        $this->handler->expects($this->once())
            ->method('handle')
            ->willReturn($this->response);

        $this->response->expects($this->exactly(2))
            ->method('withHeader')
            ->willReturnSelf();

        $middleware = new ThrottleRequests($this->rate_limiter);
        $middleware->process($this->request, $this->handler);
    }

    #[Test]
    public function processUsesDefaultIpWhenNotAvailable(): void
    {
        $this->request->expects($this->exactly(2))
            ->method('getAttribute')
            ->willReturnCallback(function ($key): null {
                return null;
            });

        $result = RateLimitResult::allowed(
            remaining_per_second: 9,
            remaining_per_minute: 59,
            reset_time: new DateTimeImmutable('+1 minute'),
            rate_limits: new RateLimits(id: 'ip:127.0.0.1', per_second: 10, per_minute: 60),
        );

        $this->rate_limiter->expects($this->once())
            ->method('throttle')
            ->with($this->callback(function (RateLimits $limits): bool {
                return $limits->id === 'ip:127.0.0.1';
            }))
            ->willReturn($result);

        $this->handler->expects($this->once())
            ->method('handle')
            ->willReturn($this->response);

        $this->response->expects($this->exactly(2))
            ->method('withHeader')
            ->willReturnSelf();

        $middleware = new ThrottleRequests($this->rate_limiter);
        $middleware->process($this->request, $this->handler);
    }

    #[Test]
    public function processReturnsTooManyRequestsResponseWhenBlocked(): void
    {
        $rate_limits = new RateLimits(id: 'test-user');

        $this->request->expects($this->once())
            ->method('getAttribute')
            ->with(RateLimits::class)
            ->willReturn($rate_limits);

        $result = RateLimitResult::blocked(
            reset_time: new DateTimeImmutable('@1642636860'), // +1 minute from timestamp
            rate_limits: $rate_limits,
        );

        $this->rate_limiter->expects($this->once())
            ->method('throttle')
            ->willReturn($result);

        $this->handler->expects($this->never())
            ->method('handle');

        $middleware = new ThrottleRequests($this->rate_limiter);
        $response = $middleware->process($this->request, $this->handler);

        self::assertInstanceOf(TooManyRequestsResponse::class, $response);
    }

    #[Test]
    public function processAddsCorrectHeadersToSuccessfulResponse(): void
    {
        $rate_limits = new RateLimits(id: 'test-user', per_second: 15, per_minute: 90);

        $this->request->expects($this->once())
            ->method('getAttribute')
            ->with(RateLimits::class)
            ->willReturn($rate_limits);

        $result = RateLimitResult::allowed(
            remaining_per_second: 14,
            remaining_per_minute: 89,
            reset_time: new DateTimeImmutable('@1642636860'),
            rate_limits: $rate_limits,
        );

        $this->rate_limiter->expects($this->once())
            ->method('throttle')
            ->willReturn($result);

        $this->handler->expects($this->once())
            ->method('handle')
            ->willReturn($this->response);

        $this->response->expects($this->exactly(2))
            ->method('withHeader')
            ->willReturnCallback(function ($header, $value): ResponseInterface&MockObject {
                self::assertContains($header, [HttpHeader::RATELIMIT_POLICY, HttpHeader::RATELIMIT]);
                return $this->response;
            });

        $middleware = new ThrottleRequests($this->rate_limiter, 5, 30);
        $middleware->process($this->request, $this->handler);
    }

    #[Test]
    public function processUsesCustomDefaultLimits(): void
    {
        $this->request->expects($this->exactly(2))
            ->method('getAttribute')
            ->willReturnCallback(function ($key): string|null {
                if ($key === RateLimits::class) {
                    return null;
                }
                if ($key === 'ip_address') {
                    return '1.2.3.4';
                }
                return null;
            });

        $result = RateLimitResult::allowed(
            remaining_per_second: 4,
            remaining_per_minute: 29,
            reset_time: new DateTimeImmutable('+1 minute'),
            rate_limits: new RateLimits(id: 'ip:1.2.3.4', per_second: 5, per_minute: 30),
        );

        $this->rate_limiter->expects($this->once())
            ->method('throttle')
            ->with($this->callback(function (RateLimits $limits): bool {
                return $limits->per_second === 5 && $limits->per_minute === 30;
            }))
            ->willReturn($result);

        $this->handler->expects($this->once())
            ->method('handle')
            ->willReturn($this->response);

        $this->response->expects($this->exactly(2))
            ->method('withHeader')
            ->willReturnSelf();

        $middleware = new ThrottleRequests($this->rate_limiter, 5, 30);
        $middleware->process($this->request, $this->handler);
    }
}
