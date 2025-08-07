<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Event;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;
use PhoneBurner\Pinch\Component\Http\Domain\HttpMethod;
use PhoneBurner\Pinch\Component\Http\Event\HandlingHttpRequestFailed;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\LogLevel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HandlingHttpRequestFailedTest extends TestCase
{
    #[Test]
    public function constructorSetsPublicProperties(): void
    {
        $request = new ServerRequest(
            uri: new Uri('https://example.com/test'),
            method: HttpMethod::Post->value,
        );
        $exception = new \Exception('Test exception');

        $event = new HandlingHttpRequestFailed($request, $exception);

        self::assertSame($request, $event->request);
        self::assertSame($exception, $event->e);
    }

    #[Test]
    public function constructorAcceptsNullRequest(): void
    {
        $exception = new \Exception('Test exception');

        $event = new HandlingHttpRequestFailed(null, $exception);

        self::assertNull($event->request);
        self::assertSame($exception, $event->e);
    }

    #[Test]
    public function getLogEntryReturnsLogEntryWithRequestDetailsAndException(): void
    {
        $request = new ServerRequest(
            uri: new Uri('https://example.com/test'),
            method: HttpMethod::Post->value,
        );
        $exception = new \Exception('Test exception');

        $event = new HandlingHttpRequestFailed($request, $exception);
        $log_entry = $event->getLogEntry();

        self::assertInstanceOf(LogEntry::class, $log_entry);
        self::assertSame(LogLevel::Error, $log_entry->level);
        self::assertSame('HTTP Request Handling Failed', $log_entry->message);
        self::assertArrayHasKey('method', $log_entry->context);
        self::assertArrayHasKey('uri', $log_entry->context);
        self::assertArrayHasKey('exception', $log_entry->context);
        self::assertSame(HttpMethod::Post->value, $log_entry->context['method']);
        self::assertSame('https://example.com/test', $log_entry->context['uri']);
        self::assertSame($exception, $log_entry->context['exception']);
    }

    #[Test]
    public function getLogEntryHandlesNullRequest(): void
    {
        $exception = new \Exception('Test exception');

        $event = new HandlingHttpRequestFailed(null, $exception);
        $log_entry = $event->getLogEntry();

        self::assertInstanceOf(LogEntry::class, $log_entry);
        self::assertSame(LogLevel::Error, $log_entry->level);
        self::assertSame('HTTP Request Handling Failed', $log_entry->message);
        self::assertArrayHasKey('method', $log_entry->context);
        self::assertArrayHasKey('uri', $log_entry->context);
        self::assertArrayHasKey('exception', $log_entry->context);
        self::assertNull($log_entry->context['method']);
        self::assertSame('', $log_entry->context['uri']);
        self::assertSame($exception, $log_entry->context['exception']);
    }
}
