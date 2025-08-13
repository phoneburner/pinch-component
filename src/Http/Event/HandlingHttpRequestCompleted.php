<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Event;

use PhoneBurner\Pinch\Attribute\Psr14Event;
use PhoneBurner\Pinch\Component\Http\RequestAware;
use PhoneBurner\Pinch\Component\Http\ResponseAware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[Psr14Event]
final readonly class HandlingHttpRequestCompleted implements RequestAware, ResponseAware
{
    public function __construct(public ServerRequestInterface $request, public ResponseInterface $response)
    {
    }
}
