<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\HttpClient\Event;

use Laminas\Diactoros\Request;
use PhoneBurner\Pinch\Component\Http\Domain\HttpMethod;
use PhoneBurner\Pinch\Component\HttpClient\Event\HttpClientRequestFailed;
use PhoneBurner\Pinch\Component\HttpClient\Exception\HttpClientException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;

final class HttpClientRequestFailedTest extends TestCase
{
    #[Test]
    public function constructorSetsPublicProperties(): void
    {
        $request = new Request('https://example.com/test', HttpMethod::Post->value);
        $exception = new HttpClientException('Connection timeout');
        $event = new HttpClientRequestFailed($request, $exception);

        self::assertSame($request, $event->request);
        self::assertSame($exception, $event->exception);
    }

    #[Test]
    public function getLogEntryReturnsLogEntryWithRequestAndExceptionDetails(): void
    {
        $request = new Request('https://example.com/test', HttpMethod::Post->value);
        $exception = new HttpClientException('Connection timeout');
        $event = new HttpClientRequestFailed($request, $exception);
        $log_entry = $event->getLogEntry();

        self::assertSame('HTTP Client Request Failed', $log_entry->message);
        self::assertArrayHasKey('method', $log_entry->context);
        self::assertArrayHasKey('uri', $log_entry->context);
        self::assertArrayHasKey('exception', $log_entry->context);
        self::assertArrayHasKey('message', $log_entry->context);
        self::assertSame(HttpMethod::Post->value, $log_entry->context['method']);
        self::assertSame('https://example.com/test', $log_entry->context['uri']);
        self::assertSame(HttpClientException::class, $log_entry->context['exception']);
        self::assertSame('Connection timeout', $log_entry->context['message']);
    }

    #[Test]
    public function getLogEntryWorksWithMinimalRequestAndException(): void
    {
        $request = new Request('https://example.com', HttpMethod::Get->value);
        $exception = new HttpClientException('Error');
        $event = new HttpClientRequestFailed($request, $exception);
        $log_entry = $event->getLogEntry();

        self::assertSame('HTTP Client Request Failed', $log_entry->message);
        self::assertSame(HttpMethod::Get->value, $log_entry->context['method']);
        self::assertSame('https://example.com', $log_entry->context['uri']);
        self::assertSame(HttpClientException::class, $log_entry->context['exception']);
        self::assertSame('Error', $log_entry->context['message']);
    }

    #[Test]
    public function getLogEntryHandlesDifferentExceptionTypes(): void
    {
        $request = new Request('https://api.example.com/users', HttpMethod::Delete->value);

        // Create a mock implementation of a PSR-18 exception interface
        $exception = new class ('Custom error message') extends \RuntimeException implements ClientExceptionInterface {
        };

        $event = new HttpClientRequestFailed($request, $exception);
        $log_entry = $event->getLogEntry();

        self::assertSame($exception::class, $log_entry->context['exception']);
        self::assertSame('Custom error message', $log_entry->context['message']);
    }

    #[Test]
    public function getLogEntryWorksWithEmptyExceptionMessage(): void
    {
        $request = new Request('https://api.example.com', HttpMethod::Patch->value);
        $exception = new HttpClientException('');
        $event = new HttpClientRequestFailed($request, $exception);
        $log_entry = $event->getLogEntry();

        self::assertSame('', $log_entry->context['message']);
        self::assertSame(HttpClientException::class, $log_entry->context['exception']);
    }
}
