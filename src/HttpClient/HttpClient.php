<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\HttpClient;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * HTTP Client interface extending PSR-18 with event awareness
 */
#[Contract]
interface HttpClient extends ClientInterface
{
    /**
     * Sends a PSR-7 request and returns a PSR-7 response
     *
     * This method emits the following events:
     * - HttpClientRequestStart: Before sending the request
     * - HttpClientRequestComplete: After receiving the response
     * - HttpClientRequestFailed: If the request fails
     *
     * @throws ClientExceptionInterface
     */
    public function sendRequest(RequestInterface $request): ResponseInterface;
}
