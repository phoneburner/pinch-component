<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Response;

use Laminas\Diactoros\Stream;
use PhoneBurner\Pinch\Component\Http\Domain\HttpStatus;
use PhoneBurner\Pinch\Component\Http\Response\StreamResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

final class StreamResponseTest extends TestCase
{
    #[Test]
    public function makeCreatesResponseFromString(): void
    {
        $content = 'Hello, World!';
        $response = StreamResponse::make($content);

        self::assertSame(HttpStatus::OK, $response->getStatusCode());
        self::assertSame($content, (string)$response->getBody());
    }

    #[Test]
    public function makeCreatesResponseFromStream(): void
    {
        $content = 'Hello, Stream World!';
        $stream = new Stream('php://temp', 'w+');
        $stream->write($content);
        $stream->rewind();

        $response = StreamResponse::make($stream);

        self::assertSame(HttpStatus::OK, $response->getStatusCode());
        self::assertSame($content, (string)$response->getBody());
        self::assertInstanceOf(StreamInterface::class, $response->getBody());
    }

    #[Test]
    public function makeCreatesResponseWithCustomStatus(): void
    {
        $content = 'Created!';
        $response = StreamResponse::make($content, HttpStatus::CREATED);

        self::assertSame(HttpStatus::CREATED, $response->getStatusCode());
        self::assertSame($content, (string)$response->getBody());
    }

    #[Test]
    public function makeCreatesResponseWithCustomHeaders(): void
    {
        $content = 'Test Content';
        $headers = [
            'X-Test-Header' => ['test-value'],
            'X-Another-Header' => ['another-value'],
        ];

        $response = StreamResponse::make($content, HttpStatus::OK, $headers);

        self::assertSame(HttpStatus::OK, $response->getStatusCode());
        self::assertSame($content, (string)$response->getBody());
        self::assertSame(['test-value'], $response->getHeader('X-Test-Header'));
        self::assertSame(['another-value'], $response->getHeader('X-Another-Header'));
    }
}
