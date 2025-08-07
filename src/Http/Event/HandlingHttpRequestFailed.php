<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Event;

use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\Loggable;
use PhoneBurner\Pinch\Component\Logging\LogLevel;
use Psr\Http\Message\ServerRequestInterface;

final readonly class HandlingHttpRequestFailed implements Loggable
{
    public function __construct(public ServerRequestInterface|null $request, public \Throwable $e)
    {
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(LogLevel::Error, message: 'HTTP Request Handling Failed', context: [
            'method' => $this->request?->getMethod(),
            'uri' => (string)$this->request?->getUri(),
            'exception' => $this->e,
        ]);
    }
}
