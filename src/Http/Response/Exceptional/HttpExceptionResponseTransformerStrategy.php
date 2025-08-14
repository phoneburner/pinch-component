<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Response\Exceptional;

use PhoneBurner\Pinch\Component\Logging\LogTrace;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface HttpExceptionResponseTransformerStrategy
{
    /**
     * Note: we must manually remove the content-type header, because otherwise
     * Laminas will not overwrite it with the correct value if the header keys
     * have different cases.
     */
    public function transform(
        HttpExceptionResponse $exception,
        ServerRequestInterface $request,
        LogTrace $log_trace,
    ): ResponseInterface;
}
