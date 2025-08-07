<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\HttpClient\Event;

use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\Loggable;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;

final readonly class HttpClientRequestFailed implements Loggable
{
    public function __construct(
        public RequestInterface $request,
        public ClientExceptionInterface $exception,
    ) {
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(message: 'HTTP Client Request Failed', context: [
            'method' => $this->request->getMethod(),
            'uri' => (string)$this->request->getUri(),
            'exception' => $this->exception::class,
            'message' => $this->exception->getMessage(),
        ]);
    }
}
