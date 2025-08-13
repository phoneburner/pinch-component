<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\HttpClient\Event;

use PhoneBurner\Pinch\Attribute\Psr14Event;
use PhoneBurner\Pinch\Component\Http\RequestAware;
use PhoneBurner\Pinch\Component\Http\ResponseAware;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\Loggable;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

#[Psr14Event]
final readonly class HttpClientRequestCompleted implements Loggable, RequestAware, ResponseAware
{
    public function __construct(
        public RequestInterface $request,
        public ResponseInterface $response,
    ) {
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(message: 'HTTP Client Request Completed', context: [
            'method' => $this->request->getMethod(),
            'uri' => (string)$this->request->getUri(),
            'status_code' => $this->response->getStatusCode(),
            'reason_phrase' => $this->response->getReasonPhrase(),
        ]);
    }
}
