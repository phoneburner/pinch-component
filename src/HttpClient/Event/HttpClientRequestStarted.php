<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\HttpClient\Event;

use PhoneBurner\Pinch\Attribute\Psr14Event;
use PhoneBurner\Pinch\Component\Http\RequestAware;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\Loggable;
use Psr\Http\Message\RequestInterface;

#[Psr14Event]
final readonly class HttpClientRequestStarted implements Loggable, RequestAware
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
