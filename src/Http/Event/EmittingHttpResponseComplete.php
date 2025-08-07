<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Event;

use Psr\Http\Message\ResponseInterface;

final readonly class EmittingHttpResponseComplete
{
    public function __construct(public ResponseInterface $request)
    {
    }
}
