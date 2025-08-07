<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Event;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use PhoneBurner\Pinch\Component\Http\Event\MiddlewareProcessingComplete;
use PhoneBurner\Pinch\Component\Http\Middleware\LazyMiddleware;
use PhoneBurner\Pinch\Component\Http\Middleware\NullMiddleware;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;

final class MiddlewareProcessingCompleteTest extends TestCase
{
    #[Test]
    public function constructorSetsPublicProperties(): void
    {
        $request = new ServerRequest();
        $response = new Response();
        $middleware = $this->createMock(MiddlewareInterface::class);

        $event = new MiddlewareProcessingComplete($middleware, $request, $response);

        self::assertSame($middleware, $event->middleware);
        self::assertSame($request, $event->request);
        self::assertSame($response, $event->response);
    }

    #[Test]
    public function getLogEntryReturnsLogEntryWithMiddlewareClass(): void
    {
        $request = new ServerRequest();
        $response = new Response();
        $middleware = $this->createMock(MiddlewareInterface::class);

        $event = new MiddlewareProcessingComplete($middleware, $request, $response);
        $log_entry = $event->getLogEntry();

        self::assertInstanceOf(LogEntry::class, $log_entry);
        self::assertSame('Processed Request with Middleware: {middleware}', $log_entry->message);
        self::assertArrayHasKey('middleware', $log_entry->context);
        self::assertSame($middleware::class, $log_entry->context['middleware']);
    }

    #[Test]
    public function getLogEntryReturnsMiddlewarePropertyFromLazyMiddleware(): void
    {
        $request = new ServerRequest();
        $response = new Response();

        $middleware_name = NullMiddleware::class;
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->with($middleware_name)->willReturn(new NullMiddleware());
        $lazy_middleware = LazyMiddleware::make($container, $middleware_name);

        $event = new MiddlewareProcessingComplete($lazy_middleware, $request, $response);

        $log_entry = $event->getLogEntry();
        self::assertInstanceOf(LogEntry::class, $log_entry);
        self::assertArrayHasKey('middleware', $log_entry->context);
        self::assertSame($middleware_name, $log_entry->context['middleware']);
    }
}
