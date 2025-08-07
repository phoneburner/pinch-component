<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Middleware;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use PhoneBurner\Pinch\Component\Http\Middleware\LazyMiddleware;
use PhoneBurner\Pinch\Component\Http\Middleware\NullMiddleware;
use PhoneBurner\Pinch\Component\Http\Middleware\TerminableMiddleware;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class LazyMiddlewareTest extends TestCase
{
    private ContainerInterface&MockObject $container;

    private ServerRequestInterface $request;

    private RequestHandlerInterface&MockObject $handler;

    private ResponseInterface $response;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->request = new ServerRequest();
        $this->handler = $this->createMock(RequestHandlerInterface::class);
        $this->response = new Response();
    }

    #[Test]
    public function makeReturnsLazyMiddlewareInstance(): void
    {
        $middleware = LazyMiddleware::make($this->container, NullMiddleware::class);
        self::assertInstanceOf(LazyMiddleware::class, $middleware);
    }

    #[Test]
    public function processDelegatesToResolvedMiddleware(): void
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->expects($this->once())
            ->method('process')
            ->with($this->request, $this->handler)
            ->willReturn($this->response);

        $this->container->expects($this->once())
            ->method('get')
            ->with(NullMiddleware::class)
            ->willReturn($middleware);

        $lazy_middleware = LazyMiddleware::make($this->container, NullMiddleware::class);

        self::assertSame($this->response, $lazy_middleware->process($this->request, $this->handler));
    }

    #[Test]
    public function processSetsFallbackHandlerOnTerminableMiddleware(): void
    {
        $middleware = $this->createMock(TerminableMiddleware::class);
        $middleware->expects($this->once())
            ->method('setFallbackRequestHandler')
            ->with($this->handler);

        $middleware->expects($this->once())
            ->method('process')
            ->with($this->request, $this->handler)
            ->willReturn($this->response);

        $this->container->expects($this->once())
            ->method('get')
            ->with(NullMiddleware::class)
            ->willReturn($middleware);

        $lazy_middleware = LazyMiddleware::make($this->container, NullMiddleware::class);
        $lazy_middleware->setFallbackRequestHandler($this->handler);

        self::assertSame($this->response, $lazy_middleware->process($this->request, $this->handler));
    }

    #[Test]
    public function processDoesNotSetFallbackHandlerOnNonTerminableMiddleware(): void
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->expects($this->once())
            ->method('process')
            ->with($this->request, $this->handler)
            ->willReturn($this->response);

        $this->container->expects($this->once())
            ->method('get')
            ->with(NullMiddleware::class)
            ->willReturn($middleware);

        $lazy_middleware = LazyMiddleware::make($this->container, NullMiddleware::class);
        $lazy_middleware->setFallbackRequestHandler($this->handler);

        self::assertSame($this->response, $lazy_middleware->process($this->request, $this->handler));
    }
}
