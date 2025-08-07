<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\HttpClient\Event;

use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\Loggable;
use Psr\Http\Message\RequestInterface;

final readonly class HttpClientRequestStart implements Loggable
{
    public function __construct(public RequestInterface $request)
    {
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(message: 'HTTP Client Request Starting', context: [
            'method' => $this->request->getMethod(),
            'uri' => (string)$this->request->getUri(),
            'headers' => $this->request->getHeaders(),
        ]);
    }
}
