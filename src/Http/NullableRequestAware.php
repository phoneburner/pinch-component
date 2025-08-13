<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http;

use Psr\Http\Message\RequestInterface;

interface NullableRequestAware
{
    // phpcs:ignore
    public RequestInterface|null $request { get; }
}
