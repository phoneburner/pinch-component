<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Middleware;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use PhoneBurner\Pinch\Component\Http\Event\FallbackHandlerHandlingComplete;
use PhoneBurner\Pinch\Component\Http\Event\FallbackHandlerHandlingStart;
use PhoneBurner\Pinch\Component\Http\Event\MiddlewareProcessingComplete;
use PhoneBurner\Pinch\Component\Http\Event\MiddlewareProcessingStart;
use PhoneBurner\Pinch\Component\Http\Middleware\MiddlewareQueue;
use PhoneBurner\Pinch\Component\Http\Middleware\TerminableMiddleware;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class MiddlewareQueueTest extends TestCase
{
    private ServerRequestInterface $request;
    private Response $fallback_response;
    private RequestHandlerInterface&MockObject $fallback_handler;

    protected function setUp(): void
    {
        $this->request = new ServerRequest();
        $this->fallback_response = new Response();

        // Create the mock directly using PHPUnit's createMock
        $this->fallback_handler = $this->createMock(RequestHandlerInterface::class);

        // Configure the mock to return our response for any handle() call
        $this->fallback_handler
            ->method('handle')
            ->with($this->request)
            ->willReturn($this->fallback_response);
    }

    #[Test]
    public function makeReturnsMiddlewareQueueInstance(): void
    {
        self::assertInstanceOf(
            MiddlewareQueue::class,
            MiddlewareQueue::make($this->fallback_handler),
        );
    }

    #[Test]
    public function pushAddsMiddlewareToQueue(): void
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->expects($this->never())->method('process');

        $queue = MiddlewareQueue::make($this->fallback_handler);
        $result = $queue->push($middleware);

        self::assertSame($queue, $result);
    }

    #[Test]
    public function handleReturnsFallbackResponseWhenQueueIsEmpty(): void
    {
        $queue = MiddlewareQueue::make($this->fallback_handler);

        $response = $queue->handle($this->request);

        self::assertSame($this->fallback_response, $response);
    }

    #[Test]
    public function handleProcessesMiddlewareInOrder(): void
    {
        $first_response = new Response();
        new Response();

        $first_middleware = $this->createMock(MiddlewareInterface::class);
        $second_middleware = $this->createMock(MiddlewareInterface::class);

        // First middleware will be processed first (FIFO)
        $first_middleware->expects($this->once())
            ->method('process')
            ->with($this->request, self::isInstanceOf(RequestHandlerInterface::class))
            ->willReturn($first_response);

        // Second middleware won't be processed since first one returns a response
        $second_middleware->expects($this->never())->method('process');

        $queue = MiddlewareQueue::make($this->fallback_handler);
        $queue->push($first_middleware);
        $queue->push($second_middleware);

        $response = $queue->handle($this->request);

        self::assertSame($first_response, $response);
    }

    #[Test]
    public function handleDispatchesEventsForMiddlewareProcessing(): void
    {
        $middleware_response = new Response();
        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->method('process')->willReturn($middleware_response);

        $event_dispatcher = $this->createMock(EventDispatcherInterface::class);

        // The event dispatcher will receive events in this order
        $event_dispatcher->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function ($event) use ($middleware, $middleware_response): MiddlewareProcessingComplete|MiddlewareProcessingStart {
                static $call_count = 0;
                ++$call_count;

                if ($call_count === 1) {
                    self::assertInstanceOf(MiddlewareProcessingStart::class, $event);
                    self::assertSame($middleware, $event->middleware);
                    self::assertSame($this->request, $event->request);
                } else {
                    self::assertInstanceOf(MiddlewareProcessingComplete::class, $event);
                    self::assertSame($middleware, $event->middleware);
                    self::assertSame($this->request, $event->request);
                    self::assertSame($middleware_response, $event->response);
                }

                return $event;
            });

        $queue = MiddlewareQueue::make($this->fallback_handler, $event_dispatcher);
        $queue->push($middleware);

        $response = $queue->handle($this->request);

        self::assertSame($middleware_response, $response);
    }

    #[Test]
    public function handleDispatchesEventsForFallbackHandler(): void
    {
        $event_dispatcher = $this->createMock(EventDispatcherInterface::class);

        // The event dispatcher will receive events in this order
        $event_dispatcher->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function ($event): FallbackHandlerHandlingComplete|FallbackHandlerHandlingStart {
                static $call_count = 0;
                ++$call_count;

                if ($call_count === 1) {
                    self::assertInstanceOf(FallbackHandlerHandlingStart::class, $event);
                    self::assertSame($this->fallback_handler, $event->request_handler);
                    self::assertSame($this->request, $event->request);
                } else {
                    self::assertInstanceOf(FallbackHandlerHandlingComplete::class, $event);
                    self::assertSame($this->fallback_handler, $event->request_handler);
                    self::assertSame($this->request, $event->request);
                    self::assertSame($this->fallback_response, $event->response);
                }

                return $event;
            });

        $queue = MiddlewareQueue::make($this->fallback_handler, $event_dispatcher);

        $response = $queue->handle($this->request);

        self::assertSame($this->fallback_response, $response);
    }

    #[Test]
    public function handleSetsFallbackHandlerOnTerminableMiddleware(): void
    {
        $middleware = $this->createMock(TerminableMiddleware::class);
        $middleware->expects($this->once())
            ->method('setFallbackRequestHandler')
            ->with($this->fallback_handler);

        $middleware->method('process')->willReturn($this->fallback_response);

        $queue = MiddlewareQueue::make($this->fallback_handler);
        $queue->push($middleware);

        $queue->handle($this->request);
    }
}
