<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Event;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;
use PhoneBurner\Pinch\Component\Http\Domain\HttpMethod;
use PhoneBurner\Pinch\Component\Http\Event\HandlingHttpRequestStart;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HandlingHttpRequestStartTest extends TestCase
{
    #[Test]
    public function constructorSetsPublicProperties(): void
    {
        $request = new ServerRequest(
            uri: new Uri('https://example.com/test'),
            method: HttpMethod::Post->value,
        );
        $event = new HandlingHttpRequestStart($request);

        self::assertSame($request, $event->request);
    }

    #[Test]
    public function getLogEntryReturnsLogEntryWithRequestDetails(): void
    {
        $request = new ServerRequest(
            uri: new Uri('https://example.com/test'),
            method: HttpMethod::Post->value,
        );
        $event = new HandlingHttpRequestStart($request);
        $log_entry = $event->getLogEntry();

        self::assertInstanceOf(LogEntry::class, $log_entry);
        self::assertSame('HTTP Request Received', $log_entry->message);
        self::assertArrayHasKey('method', $log_entry->context);
        self::assertArrayHasKey('uri', $log_entry->context);
        self::assertSame(HttpMethod::Post->value, $log_entry->context['method']);
        self::assertSame('https://example.com/test', $log_entry->context['uri']);
    }
}
