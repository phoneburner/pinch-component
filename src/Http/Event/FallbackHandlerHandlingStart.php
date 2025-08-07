<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Event;

use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\Loggable;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class FallbackHandlerHandlingStart implements Loggable
{
    public function __construct(
        public RequestHandlerInterface $request_handler,
        public ServerRequestInterface $request,
    ) {
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(
            message: 'Handling Request with Fallback Handler: {fallback_handler}',
            context: [
                'fallback_handler' => $this->request_handler::class,
            ],
        );
    }
}
