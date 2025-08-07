<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Middleware;

use PhoneBurner\Pinch\Component\Http\Middleware\LazyMiddlewareRequestHandlerFactory;
use PhoneBurner\Pinch\Component\Http\Middleware\MiddlewareQueue;
use PhoneBurner\Pinch\Component\Http\Middleware\MiddlewareStack;
use PhoneBurner\Pinch\Component\Http\Middleware\NullMiddleware;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class LazyMiddlewareRequestHandlerFactoryTest extends TestCase
{
    private ContainerInterface&MockObject $container;

    private EventDispatcherInterface&MockObject $event_dispatcher;

    private RequestHandlerInterface&MockObject $fallback_handler;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->event_dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->fallback_handler = $this->createMock(RequestHandlerInterface::class);
    }

    #[Test]
    public function queueReturnsMiddlewareQueueWithResolvedMiddleware(): void
    {
        $middleware = new NullMiddleware();
        $this->container->method('get')
            ->with(NullMiddleware::class)
            ->willReturn($middleware);

        $factory = new LazyMiddlewareRequestHandlerFactory($this->container, $this->event_dispatcher);
        $queue = $factory->queue($this->fallback_handler, [NullMiddleware::class]);

        self::assertInstanceOf(MiddlewareQueue::class, $queue);
        $reflect = new \ReflectionClass($queue);
        $next = $reflect->getMethod('next')->invoke($queue);
        self::assertInstanceOf(MiddlewareInterface::class, $next);
        self::assertEquals($middleware, $next);
    }

    #[Test]
    public function stackReturnsMiddlewareStackWithResolvedMiddleware(): void
    {
        $middleware = new NullMiddleware();
        $this->container->method('get')
            ->with(NullMiddleware::class)
            ->willReturn($middleware);

        $factory = new LazyMiddlewareRequestHandlerFactory($this->container, $this->event_dispatcher);
        $stack = $factory->stack($this->fallback_handler, [NullMiddleware::class]);

        self::assertInstanceOf(MiddlewareStack::class, $stack);
        $reflect = new \ReflectionClass($stack);
        $next = $reflect->getMethod('next')->invoke($stack);
        self::assertInstanceOf(MiddlewareInterface::class, $next);
        self::assertEquals($middleware, $next);
    }

    #[Test]
    public function queueHandlesMiddlewareInstancesDirectly(): void
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $this->container->expects($this->never())
            ->method('get');

        $factory = new LazyMiddlewareRequestHandlerFactory($this->container, $this->event_dispatcher);
        $queue = $factory->queue($this->fallback_handler, [$middleware]);

        self::assertInstanceOf(MiddlewareQueue::class, $queue);
    }

    #[Test]
    public function stackHandlesMiddlewareInstancesDirectly(): void
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $this->container->expects($this->never())
            ->method('get');

        $factory = new LazyMiddlewareRequestHandlerFactory($this->container, $this->event_dispatcher);
        $stack = $factory->stack($this->fallback_handler, [$middleware]);

        self::assertInstanceOf(MiddlewareStack::class, $stack);
    }
}
