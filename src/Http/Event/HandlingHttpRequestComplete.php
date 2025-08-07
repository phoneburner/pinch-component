<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Event;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class HandlingHttpRequestComplete
{
    public function __construct(public ServerRequestInterface $request, public ResponseInterface $response)
    {
    }
}
