<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Middleware;

use PhoneBurner\Pinch\Component\Http\Domain\HttpHeader;
use PhoneBurner\Pinch\Component\Http\Domain\RateLimits;
use PhoneBurner\Pinch\Component\Http\RateLimiter\RateLimiter;
use PhoneBurner\Pinch\Component\Http\Response\Exceptional\TooManyRequestsResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware to throttle HTTP requests based on rate limits
 *
 * Checks for RateLimits in request attributes or uses default IP-based limits.
 * Uses injected RateLimiter to enforce per-second and per-minute limits.
 * Returns TooManyRequestsResponse with proper rate limit headers when exceeded.
 */
final readonly class ThrottleRequests implements MiddlewareInterface
{
    public function __construct(
        private RateLimiter $rate_limiter,
        private int $default_per_second = 10,
        private int $default_per_minute = 60,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Check for existing RateLimits in request attributes
        $rate_limits = $request->getAttribute(RateLimits::class);

        if (! $rate_limits instanceof RateLimits) {
            $rate_limits = $this->createDefaultRateLimits($request);
        }

        $result = $this->rate_limiter->throttle($rate_limits);

        if (! $result->allowed) {
            return new TooManyRequestsResponse(
                headers: [
                    HttpHeader::RATELIMIT_POLICY => \sprintf('%d;w=1, %d;w=60', $result->rate_limits->per_second, $result->rate_limits->per_minute),
                    HttpHeader::RATELIMIT => \sprintf(
                        'limit=%d, remaining=%d, reset=%d',
                        $result->rate_limits->per_second,
                        $result->remaining_per_second,
                        $result->reset_time->getTimestamp(),
                    ),
                    HttpHeader::RETRY_AFTER => (string)$result->getRetryAfterSeconds(),
                ],
            );
        }

        // Add rate limit headers to successful responses
        $response = $handler->handle($request);

        return $response
            ->withHeader(HttpHeader::RATELIMIT_POLICY, \sprintf('%d;w=1, %d;w=60', $result->rate_limits->per_second, $result->rate_limits->per_minute))
            ->withHeader(HttpHeader::RATELIMIT, \sprintf(
                'limit=%d, remaining=%d, reset=%d',
                $result->rate_limits->per_second,
                $result->remaining_per_second,
                $result->reset_time->getTimestamp(),
            ));
    }

    /**
     * Create default rate limits based on client IP from request attributes
     */
    private function createDefaultRateLimits(ServerRequestInterface $request): RateLimits
    {
        // Get IP address from request attributes (set by RequestFactory)
        $client_ip = $request->getAttribute('ip_address') ?? '127.0.0.1';

        return new RateLimits(
            id: 'ip:' . $client_ip,
            per_second: $this->default_per_second,
            per_minute: $this->default_per_minute,
        );
    }
}
