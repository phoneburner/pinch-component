<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Event;

use PhoneBurner\Pinch\Attribute\Psr14Event;
use PhoneBurner\Pinch\Component\Http\RequestAware;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\Loggable;
use Psr\Http\Message\ServerRequestInterface;

#[Psr14Event]
final readonly class HandlingHttpRequestStarted implements Loggable, RequestAware
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
