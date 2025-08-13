<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Middleware;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use PhoneBurner\Pinch\Component\Http\Event\FallbackHandlerHandlingCompleted;
use PhoneBurner\Pinch\Component\Http\Event\FallbackHandlerHandlingStarted;
use PhoneBurner\Pinch\Component\Http\Event\MiddlewareProcessingCompleted;
use PhoneBurner\Pinch\Component\Http\Event\MiddlewareProcessingStarted;
use PhoneBurner\Pinch\Component\Http\Middleware\MiddlewareStack;
use PhoneBurner\Pinch\Component\Http\Middleware\TerminableMiddleware;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class MiddlewareStackTest extends TestCase
{
    private ServerRequestInterface $request;

    private Response $fallback_response;

    private RequestHandlerInterface $fallback_handler;

    protected function setUp(): void
    {
        $this->request = new ServerRequest();
        $this->fallback_response = new Response();
        $this->fallback_handler = $this->createMock(RequestHandlerInterface::class);
        $this->fallback_handler->method('handle')
            ->with($this->request)
            ->willReturn($this->fallback_response);
    }

    #[Test]
    public function makeReturnsMiddlewareStackInstance(): void
    {
        self::assertInstanceOf(
            MiddlewareStack::class,
            MiddlewareStack::make($this->fallback_handler),
        );
    }

    #[Test]
    public function pushAddsMiddlewareToStack(): void
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->expects($this->never())->method('process');

        $stack = MiddlewareStack::make($this->fallback_handler);
        $result = $stack->push($middleware);

        self::assertSame($stack, $result);
    }

    #[Test]
    public function handleReturnsFallbackResponseWhenStackIsEmpty(): void
    {
        $stack = MiddlewareStack::make($this->fallback_handler);

        $response = $stack->handle($this->request);

        self::assertSame($this->fallback_response, $response);
    }

    #[Test]
    public function handleProcessesMiddlewareInReverseOrder(): void
    {
        new Response();
        $second_response = new Response();

        $first_middleware = $this->createMock(MiddlewareInterface::class);
        $second_middleware = $this->createMock(MiddlewareInterface::class);

        // Second middleware will be processed first (LIFO)
        $second_middleware->expects($this->once())
            ->method('process')
            ->with($this->request, self::isInstanceOf(RequestHandlerInterface::class))
            ->willReturn($second_response);

        // First middleware won't be processed since second one returns a response
        $first_middleware->expects($this->never())->method('process');

        $stack = MiddlewareStack::make($this->fallback_handler);
        $stack->push($first_middleware);
        $stack->push($second_middleware);

        $response = $stack->handle($this->request);

        self::assertSame($second_response, $response);
    }

    #[Test]
    public function handleDispatchesEventsForMiddlewareProcessing(): void
    {
        $middleware_response = new Response();
        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->method('process')->willReturn($middleware_response);

        $event_dispatcher = $this->createMock(EventDispatcherInterface::class);
        $event_dispatcher->expects($matcher = $this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function ($event) use ($middleware, $middleware_response, $matcher): MiddlewareProcessingStarted|MiddlewareProcessingCompleted {
                if ($matcher->numberOfInvocations() === 1) {
                    self::assertInstanceOf(MiddlewareProcessingStarted::class, $event);
                    self::assertSame($middleware, $event->middleware);
                    self::assertSame($this->request, $event->request);
                    return $event;
                }

                if ($matcher->numberOfInvocations() === 2) {
                    self::assertInstanceOf(MiddlewareProcessingCompleted::class, $event);
                    self::assertSame($middleware, $event->middleware);
                    self::assertSame($this->request, $event->request);
                    self::assertSame($middleware_response, $event->response);
                    return $event;
                }

                self::fail('Unexpected event dispatched');
            });

        $stack = MiddlewareStack::make($this->fallback_handler, $event_dispatcher);
        $stack->push($middleware);

        $response = $stack->handle($this->request);

        self::assertSame($middleware_response, $response);
    }

    #[Test]
    public function handleDispatchesEventsForFallbackHandler(): void
    {
        $event_dispatcher = $this->createMock(EventDispatcherInterface::class);
        $event_dispatcher->expects($matcher = $this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function ($event) use ($matcher): FallbackHandlerHandlingStarted|FallbackHandlerHandlingCompleted {
                if ($matcher->numberOfInvocations() === 1) {
                    self::assertInstanceOf(FallbackHandlerHandlingStarted::class, $event);
                    self::assertSame($this->fallback_handler, $event->request_handler);
                    self::assertSame($this->request, $event->request);
                    return $event;
                }

                if ($matcher->numberOfInvocations() === 2) {
                    self::assertInstanceOf(FallbackHandlerHandlingCompleted::class, $event);
                    self::assertSame($this->fallback_handler, $event->request_handler);
                    self::assertSame($this->request, $event->request);
                    return $event;
                }

                self::fail('Unexpected event dispatched');
            });

        $stack = MiddlewareStack::make($this->fallback_handler, $event_dispatcher);

        $response = $stack->handle($this->request);

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

        $stack = MiddlewareStack::make($this->fallback_handler);
        $stack->push($middleware);

        $stack->handle($this->request);
    }
}
