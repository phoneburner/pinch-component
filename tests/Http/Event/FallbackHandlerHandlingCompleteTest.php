<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Event;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use PhoneBurner\Pinch\Component\Http\Event\FallbackHandlerHandlingComplete;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

final class FallbackHandlerHandlingCompleteTest extends TestCase
{
    #[Test]
    public function constructorSetsPublicProperties(): void
    {
        $request = new ServerRequest();
        $response = new Response();
        $request_handler = $this->createMock(RequestHandlerInterface::class);

        $event = new FallbackHandlerHandlingComplete(
            $request_handler,
            $request,
            $response,
        );

        self::assertSame($request_handler, $event->request_handler);
        self::assertSame($request, $event->request);
        self::assertSame($response, $event->response);
    }

    #[Test]
    public function getLogEntryReturnsLogEntryWithFallbackHandlerClass(): void
    {
        $request = new ServerRequest();
        $response = new Response();
        $request_handler = $this->createMock(RequestHandlerInterface::class);

        $event = new FallbackHandlerHandlingComplete(
            $request_handler,
            $request,
            $response,
        );
        $log_entry = $event->getLogEntry();

        self::assertInstanceOf(LogEntry::class, $log_entry);
        self::assertSame('Handled Request with Fallback Handler: {fallback_handler}', $log_entry->message);
        self::assertArrayHasKey('fallback_handler', $log_entry->context);
        self::assertSame($request_handler::class, $log_entry->context['fallback_handler']);
    }
}
