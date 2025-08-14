<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware to throttle HTTP requests based on rate limits
 *
 * Checks for RateLimits in request attributes or uses default IP-based limits.
 * Uses injected RateLimiter to enforce per-second and per-minute limits.
 * Returns TooManyRequestsResponse with the proper rate limit headers when exceeded.
 */
final class NullThrottleRequests extends ThrottleRequests
{
    public function __construct()
    {
    }

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
    }
}
