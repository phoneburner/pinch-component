<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Event;

use PhoneBurner\Pinch\Attribute\Psr14Event;
use PhoneBurner\Pinch\Component\Http\ResponseAware;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\Loggable;
use PhoneBurner\Pinch\Component\Logging\LogLevel;
use Psr\Http\Message\ResponseInterface;

#[Psr14Event]
final readonly class EmittingHttpResponseFailed implements Loggable, ResponseAware
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
