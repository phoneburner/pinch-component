<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http;

use Psr\Http\Message\ResponseInterface;

interface NullableResponseAware
{
    // phpcs:ignore
    public ResponseInterface|null $response { get; }
}
