<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Event;

use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\Loggable;
use Psr\Http\Message\ServerRequestInterface;

final readonly class HandlingHttpRequestStart implements Loggable
{
    public function __construct(public ServerRequestInterface $request)
    {
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(message: 'HTTP Request Received', context: [
            'method' => $this->request->getMethod(),
            'uri' => (string)$this->request->getUri(),
        ]);
    }
}
