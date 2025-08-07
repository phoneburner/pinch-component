<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Routing\RequestHandler;

use Laminas\Diactoros\Uri;
use PhoneBurner\Pinch\Component\Http\Response\Exceptional\NotFoundResponse;
use PhoneBurner\Pinch\Component\Http\Routing\RequestHandler\NotFoundRequestHandler;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class NotFoundRequestHandlerTest extends TestCase
{
    #[Test]
    public function handleReturnsPageNotFound(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(new Uri('https://example.com/test/path?with=query'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('notice')
            ->willReturnCallback(static function ($message, array $context): void {
                self::assertSame('Not Found: {path}', $message);
                self::assertSame('https://example.com/test/path?with=query', $context['path']);
            });

        $sut = new NotFoundRequestHandler($logger);

        $response = $sut->handle($request);

        self::assertInstanceOf(NotFoundResponse::class, $response);
    }
}
