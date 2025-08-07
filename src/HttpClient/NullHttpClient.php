<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\HttpClient;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Null implementation of HTTP Client that returns empty responses
 */
final readonly class NullHttpClient implements HttpClient
{
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return throw new \LogicException('NullHttpClient does not support sending requests');
    }
}
