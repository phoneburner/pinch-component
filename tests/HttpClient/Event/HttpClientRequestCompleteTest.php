<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\HttpClient\Event;

use Laminas\Diactoros\Request;
use PhoneBurner\Pinch\Component\Http\Domain\HttpMethod;
use PhoneBurner\Pinch\Component\Http\Response\StreamResponse;
use PhoneBurner\Pinch\Component\HttpClient\Event\HttpClientRequestComplete;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HttpClientRequestCompleteTest extends TestCase
{
    #[Test]
    public function constructorSetsPublicProperties(): void
    {
        $request = new Request('https://example.com/test', HttpMethod::Post->value);
        $response = StreamResponse::make('success 123', status: 200);
        $event = new HttpClientRequestComplete($request, $response);

        self::assertSame($request, $event->request);
        self::assertSame($response, $event->response);
    }

    #[Test]
    public function getLogEntryReturnsLogEntryWithRequestAndResponseDetails(): void
    {
        $request = new Request('https://example.com/test', HttpMethod::Post->value);
        $response = StreamResponse::make('created', status: 201, headers: ['Content-Type' => 'application/json']);
        $event = new HttpClientRequestComplete($request, $response);
        $log_entry = $event->getLogEntry();

        self::assertInstanceOf(LogEntry::class, $log_entry);
        self::assertSame('HTTP Client Request Completed', $log_entry->message);
        self::assertArrayHasKey('method', $log_entry->context);
        self::assertArrayHasKey('uri', $log_entry->context);
        self::assertArrayHasKey('status_code', $log_entry->context);
        self::assertArrayHasKey('reason_phrase', $log_entry->context);
        self::assertSame(HttpMethod::Post->value, $log_entry->context['method']);
        self::assertSame('https://example.com/test', $log_entry->context['uri']);
        self::assertSame(201, $log_entry->context['status_code']);
        self::assertSame('Created', $log_entry->context['reason_phrase']);
    }

    #[Test]
    public function getLogEntryWorksWithMinimalRequestAndResponse(): void
    {
        $request = new Request('https://example.com', HttpMethod::Get->value);
        $response = new StreamResponse();
        $event = new HttpClientRequestComplete($request, $response);
        $log_entry = $event->getLogEntry();

        self::assertInstanceOf(LogEntry::class, $log_entry);
        self::assertSame('HTTP Client Request Completed', $log_entry->message);
        self::assertSame(HttpMethod::Get->value, $log_entry->context['method']);
        self::assertSame('https://example.com', $log_entry->context['uri']);
        self::assertSame(200, $log_entry->context['status_code']);
        self::assertSame('OK', $log_entry->context['reason_phrase']);
    }

    #[Test]
    public function getLogEntryHandlesDifferentStatusCodes(): void
    {
        $request = new Request('https://api.example.com/users', HttpMethod::Delete->value);
        $response = StreamResponse::make('Not Found', status: 404);
        $event = new HttpClientRequestComplete($request, $response);
        $log_entry = $event->getLogEntry();

        self::assertSame(404, $log_entry->context['status_code']);
        self::assertSame('Not Found', $log_entry->context['reason_phrase']);
    }
}
