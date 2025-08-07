<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Response;

use PhoneBurner\Pinch\Component\Http\Domain\HttpHeader;
use PhoneBurner\Pinch\Component\Http\Domain\HttpStatus;
use PhoneBurner\Pinch\Component\Http\Response\ServerSentEventsResponse;
use PhoneBurner\Pinch\Time\TimeInterval\TimeInterval;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ServerSentEventsResponseTest extends TestCase
{
    #[Test]
    public function createsServerSentEventsResponseWithDefaults(): void
    {
        $response = new ServerSentEventsResponse(
            (fn(): \Generator => yield from ['foo', ':', 'bar', ':', 'baz'])(),
        );

        self::assertSame(HttpStatus::OK, $response->getStatusCode());
        self::assertSame('foo', $response->getBody()->read(1000));
        self::assertSame(':', $response->getBody()->read(1000));
        self::assertSame('bar', $response->getBody()->read(1000));
        self::assertSame(':', $response->getBody()->read(1000));
        self::assertSame('baz', $response->getBody()->read(1000));
        self::assertSame(['no'], $response->getHeader(HttpHeader::X_ACCEL_BUFFERING));
        self::assertSame(['text/event-stream'], $response->getHeader(HttpHeader::CONTENT_TYPE));
        self::assertSame(['no-cache'], $response->getHeader(HttpHeader::CACHE_CONTROL));
        self::assertSame(['keep-alive'], $response->getHeader(HttpHeader::CONNECTION));
        self::assertEquals(new TimeInterval(seconds: 600), $response->ttl);
    }

    #[Test]
    public function createsServerSentEventsResponseWithCustomParams(): void
    {
        $response = new ServerSentEventsResponse(
            (fn(): \Generator => yield from ['foo', ':', 'bar', ':', 'baz'])(),
            TimeInterval::max(),
            [
                'Custom-Header' => '123',
            ],
        );

        self::assertSame(HttpStatus::OK, $response->getStatusCode());
        self::assertSame('foo', $response->getBody()->read(1000));
        self::assertSame(':', $response->getBody()->read(1000));
        self::assertSame('bar', $response->getBody()->read(1000));
        self::assertSame(':', $response->getBody()->read(1000));
        self::assertSame('baz', $response->getBody()->read(1000));
        self::assertSame(['Custom-Header' => ['123']], $response->getHeaders());
        self::assertEquals(TimeInterval::max(), $response->ttl);
    }
}
