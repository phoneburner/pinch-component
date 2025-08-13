<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Event;

use PhoneBurner\Pinch\Attribute\Psr14Event;
use PhoneBurner\Pinch\Component\Http\ResponseAware;
use Psr\Http\Message\ResponseInterface;

#[Psr14Event]
final readonly class EmittingHttpResponseStarted implements ResponseAware
{
    public function __construct(public ResponseInterface $response)
    {
    }
}
