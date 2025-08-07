<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Routing\RequestHandler;

use PhoneBurner\Pinch\Component\Http\Response\Exceptional\NotFoundResponse;
use PhoneBurner\Pinch\Component\IpAddress\IpAddress;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * This should be the default fallback handler for all requests that are not
 * otherwise routed or handled by the application in some way.
 */
final readonly class NotFoundRequestHandler implements RequestHandlerInterface
{
    public function __construct(private LoggerInterface $logger = new NullLogger())
    {
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->logger->notice('Not Found: {path}', [
            'path' => (string)$request->getUri(),
            'ip_address' => $request->getAttribute(IpAddress::class),
        ]);

        return new NotFoundResponse();
    }
}
