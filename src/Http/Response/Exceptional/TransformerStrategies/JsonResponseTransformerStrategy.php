<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Response\Exceptional\TransformerStrategies;

use PhoneBurner\Pinch\Component\Http\Domain\HttpHeader;
use PhoneBurner\Pinch\Component\Http\Response\ApiProblemResponse;
use PhoneBurner\Pinch\Component\Http\Response\Exceptional\HttpExceptionResponse;
use PhoneBurner\Pinch\Component\Http\Response\Exceptional\HttpExceptionResponseTransformerStrategy;
use PhoneBurner\Pinch\Component\Logging\LogTrace;
use Psr\Http\Message\ServerRequestInterface;

final class JsonResponseTransformerStrategy implements HttpExceptionResponseTransformerStrategy
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
    ): ApiProblemResponse {
        return new ApiProblemResponse($exception->getStatusCode(), $exception->getStatusTitle(), [
            'log_trace' => $log_trace->toString(),
            'detail' => $exception->getStatusDetail() ?: null,
            ...$exception->getAdditional(),
        ], $exception->withoutHeader(HttpHeader::CONTENT_TYPE)->getHeaders());
    }
}
