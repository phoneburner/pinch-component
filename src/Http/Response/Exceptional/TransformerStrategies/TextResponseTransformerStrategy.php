<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Response\Exceptional\TransformerStrategies;

use PhoneBurner\Pinch\Component\Http\Domain\ContentType;
use PhoneBurner\Pinch\Component\Http\Domain\HttpHeader;
use PhoneBurner\Pinch\Component\Http\Response\Exceptional\GenericHttpExceptionResponse;
use PhoneBurner\Pinch\Component\Http\Response\Exceptional\HttpExceptionResponseTransformerStrategy;
use PhoneBurner\Pinch\Component\Http\Response\TextResponse;
use PhoneBurner\Pinch\Component\Logging\LogTrace;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class TextResponseTransformerStrategy implements HttpExceptionResponseTransformerStrategy
{
    public function transform(
        ResponseInterface $exception,
        ServerRequestInterface $request,
        LogTrace $log_trace,
    ): TextResponse {
        if ($exception instanceof GenericHttpExceptionResponse) {
            $exception = $exception->getWrapped();
        }

        return $exception instanceof TextResponse ? $exception : new TextResponse(
            \sprintf('HTTP %s: %s', $exception->getStatusCode(), $exception->getReasonPhrase()),
            $exception->getStatusCode(),
            [...$exception->getHeaders(), HttpHeader::CONTENT_TYPE => ContentType::TEXT],
        );
    }
}
