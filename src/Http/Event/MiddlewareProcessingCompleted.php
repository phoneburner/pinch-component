<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Event;

use PhoneBurner\Pinch\Attribute\Psr14Event;
use PhoneBurner\Pinch\Component\Http\Middleware\LazyMiddleware;
use PhoneBurner\Pinch\Component\Http\RequestAware;
use PhoneBurner\Pinch\Component\Http\ResponseAware;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\Loggable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

#[Psr14Event]
final readonly class MiddlewareProcessingCompleted implements Loggable, RequestAware, ResponseAware
{
    public function __construct(
        public MiddlewareInterface $middleware,
        public ServerRequestInterface $request,
        public ResponseInterface $response,
    ) {
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(
            message: 'Processed Request with Middleware: {middleware}',
            context: [
                'middleware' => $this->middleware instanceof LazyMiddleware ? $this->middleware->middleware : $this->middleware::class,
            ],
        );
    }
}
