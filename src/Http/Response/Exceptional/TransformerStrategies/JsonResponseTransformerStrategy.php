<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Response\Exceptional\TransformerStrategies;

use PhoneBurner\Pinch\Component\Http\Response\ApiProblemResponse;
use PhoneBurner\Pinch\Component\Http\Response\Exceptional\HttpExceptionResponse;
use PhoneBurner\Pinch\Component\Http\Response\Exceptional\HttpExceptionResponseTransformerStrategy;
use PhoneBurner\Pinch\Component\Logging\LogTrace;
use Psr\Http\Message\ServerRequestInterface;

final class JsonResponseTransformerStrategy implements HttpExceptionResponseTransformerStrategy
{
    public function transform(
        HttpExceptionResponse $exception,
        ServerRequestInterface $request,
        LogTrace $log_trace,
    ): ApiProblemResponse {
        return new ApiProblemResponse($exception->getStatusCode(), $exception->getStatusTitle(), [
            'log_trace' => $log_trace->toString(),
            'detail' => $exception->getStatusDetail() ?: null,
            ...$exception->getAdditional(),
        ], $exception->getHeaders());
    }
}
