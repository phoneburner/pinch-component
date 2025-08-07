<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Routing\RequestHandler;

use PhoneBurner\Pinch\Component\Http\Domain\HttpHeader;
use PhoneBurner\Pinch\Component\Http\Psr7;
use PhoneBurner\Pinch\Component\Http\Response\Exceptional\FileNotFoundResponse;
use PhoneBurner\Pinch\Component\Http\Response\Exceptional\ServerErrorResponse;
use PhoneBurner\Pinch\Component\Http\Response\StreamResponse;
use PhoneBurner\Pinch\Component\Http\Routing\Domain\StaticFile;
use PhoneBurner\Pinch\Component\Http\Routing\Match\RouteMatch;
use PhoneBurner\Pinch\Component\Http\Stream\FileStream;
use PhoneBurner\Pinch\Component\Http\StreamFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class StaticFileRequestHandler implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $route_attributes = Psr7::attribute(RouteMatch::class, $request)?->getAttributes() ?? [];
        $file = $route_attributes[StaticFile::class] ?? null;
        if (! $file instanceof StaticFile) {
            return new ServerErrorResponse();
        }

        $stream = StreamFactory::file($file->path);
        if (! $stream instanceof FileStream) {
            return new FileNotFoundResponse();
        }

        return new StreamResponse($stream, headers: [
            HttpHeader::CONTENT_TYPE => $file->content_type,
            HttpHeader::CONTENT_LENGTH => $stream->getSize() ?? 0,
            HttpHeader::CONTENT_DISPOSITION => $route_attributes[HttpHeader::CONTENT_DISPOSITION] ?? 'inline',
        ]);
    }
}
