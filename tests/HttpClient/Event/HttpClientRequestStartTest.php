<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\HttpClient\Event;

use Laminas\Diactoros\Request;
use PhoneBurner\Pinch\Component\Http\Domain\HttpMethod;
use PhoneBurner\Pinch\Component\Http\Stream\MemoryStream;
use PhoneBurner\Pinch\Component\HttpClient\Event\HttpClientRequestStarted;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HttpClientRequestStartTest extends TestCase
{
    #[Test]
    public function constructorSetsPublicProperties(): void
    {
        $request = new Request('https://example.com/test', HttpMethod::Post->value);
        $event = new HttpClientRequestStarted($request);

        self::assertSame($request, $event->request);
    }

    #[Test]
    public function getLogEntryReturnsLogEntryWithRequestDetails(): void
    {
        $request = new Request(
            'https://example.com/test',
            HttpMethod::Post->value,
            body: new MemoryStream('test body'),
            headers: [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer token123',
            ],
        );
        $event = new HttpClientRequestStarted($request);
        $log_entry = $event->getLogEntry();

        self::assertSame('HTTP Client Request Starting', $log_entry->message);
        self::assertArrayHasKey('method', $log_entry->context);
        self::assertArrayHasKey('uri', $log_entry->context);
        self::assertArrayHasKey('headers', $log_entry->context);
        self::assertSame(HttpMethod::Post->value, $log_entry->context['method']);
        self::assertSame('https://example.com/test', $log_entry->context['uri']);
        self::assertArrayHasKey('Content-Type', $log_entry->context['headers']);
        self::assertArrayHasKey('Authorization', $log_entry->context['headers']);
        self::assertSame(['application/json'], $log_entry->context['headers']['Content-Type']);
        self::assertSame(['Bearer token123'], $log_entry->context['headers']['Authorization']);
    }

    #[Test]
    public function getLogEntryWorksWithMinimalRequest(): void
    {
        $request = new Request('https://example.com', HttpMethod::Get->value);
        $event = new HttpClientRequestStarted($request);
        $log_entry = $event->getLogEntry();

        self::assertInstanceOf(LogEntry::class, $log_entry);
        self::assertSame('HTTP Client Request Starting', $log_entry->message);
        self::assertSame(HttpMethod::Get->value, $log_entry->context['method']);
        self::assertSame('https://example.com', $log_entry->context['uri']);
        self::assertArrayHasKey('Host', $log_entry->context['headers']);
        self::assertSame(['example.com'], $log_entry->context['headers']['Host']);
    }
}
