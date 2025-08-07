<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Event;

use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\Loggable;
use PhoneBurner\Pinch\Component\Logging\LogLevel;
use Psr\Http\Message\ResponseInterface;

final readonly class EmittingHttpResponseFailed implements Loggable
{
    public function __construct(public ResponseInterface $response, public \Throwable $e)
    {
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(LogLevel::Critical, message: 'An unhandled error occurred while emitting the request', context: [
            'exception' => $this->e,
        ]);
    }
}
