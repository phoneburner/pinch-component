<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\HttpClient;

use PhoneBurner\Pinch\Component\HttpClient\Event\HttpClientRequestComplete;
use PhoneBurner\Pinch\Component\HttpClient\Event\HttpClientRequestFailed;
use PhoneBurner\Pinch\Component\HttpClient\Event\HttpClientRequestStart;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * HTTP Client that wraps another ClientInterface and emits events
 */
final readonly class HttpClientWrapper implements HttpClient
{
    public function __construct(
        private ClientInterface $client,
        private EventDispatcherInterface $event_dispatcher,
    ) {
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->event_dispatcher->dispatch(new HttpClientRequestStart($request));

        try {
            $response = $this->client->sendRequest($request);
            $this->event_dispatcher->dispatch(new HttpClientRequestComplete($request, $response));
            return $response;
        } catch (ClientExceptionInterface $exception) {
            $this->event_dispatcher->dispatch(new HttpClientRequestFailed($request, $exception));
            throw $exception;
        }
    }
}
