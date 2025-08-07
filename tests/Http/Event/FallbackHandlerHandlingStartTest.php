<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Event;

use Laminas\Diactoros\ServerRequest;
use PhoneBurner\Pinch\Component\Http\Event\FallbackHandlerHandlingStart;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

final class FallbackHandlerHandlingStartTest extends TestCase
{
    #[Test]
    public function constructorSetsPublicProperties(): void
    {
        $request = new ServerRequest();
        $request_handler = $this->createMock(RequestHandlerInterface::class);

        $event = new FallbackHandlerHandlingStart($request_handler, $request);

        self::assertSame($request_handler, $event->request_handler);
        self::assertSame($request, $event->request);
    }

    #[Test]
    public function getLogEntryReturnsLogEntryWithFallbackHandlerClass(): void
    {
        $request = new ServerRequest();
        $request_handler = $this->createMock(RequestHandlerInterface::class);

        $event = new FallbackHandlerHandlingStart($request_handler, $request);
        $log_entry = $event->getLogEntry();

        self::assertInstanceOf(LogEntry::class, $log_entry);
        self::assertSame('Handling Request with Fallback Handler: {fallback_handler}', $log_entry->message);
        self::assertArrayHasKey('fallback_handler', $log_entry->context);
        self::assertSame($request_handler::class, $log_entry->context['fallback_handler']);
    }
}
