<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http;

use Laminas\Diactoros\StreamFactory;
use PhoneBurner\Pinch\Component\Http\Domain\HttpMethod;
use PhoneBurner\Pinch\Component\Http\Psr7;
use PhoneBurner\Pinch\Component\Http\Request\DefaultRequestFactory;
use PhoneBurner\Pinch\Component\Tests\Fixtures\MockRequestHandler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Psr7Test extends TestCase
{
    #[Test]
    public function attributeReturnsNullWhenAttributeNotFound(): void
    {
        $request = new DefaultRequestFactory()->createServerRequest(HttpMethod::Get, 'http://example.com');

        self::assertNull(Psr7::attribute(RequestHandlerInterface::class, $request));
    }

    #[Test]
    public function attributeReturnsNullWhenAttributeNotInstance(): void
    {
        $request = new DefaultRequestFactory()->createServerRequest(HttpMethod::Get, 'http://example.com', attributes: [
            RequestHandlerInterface::class => new \stdClass(),
        ]);

        self::assertNull(Psr7::attribute(RequestHandlerInterface::class, $request));
    }

    #[Test]
    public function attributeReturnsInstanceOnHappyPath(): void
    {
        $handler = new MockRequestHandler();
        $request = new DefaultRequestFactory()->createServerRequest(HttpMethod::Get, 'http://example.com', attributes: [
            RequestHandlerInterface::class => $handler,
        ]);

        self::assertSame($handler, Psr7::attribute(RequestHandlerInterface::class, $request));
    }

    #[Test]
    public function jsonBodyToArrayHappyPathWithMessage(): void
    {
        $array = [
            'foo' => 'bar',
            'baz' => 42,
        ];

        $message = $this->createMock(MessageInterface::class);
        $message->method('getBody')->willReturn(Psr7::stream(\json_encode($array, \JSON_THROW_ON_ERROR)));

        self::assertSame($array, Psr7::jsonBodyToArray($message));
    }

    #[Test]
    public function jsonBodyToArrayHappyPathWithStream(): void
    {
        $array = [
            'foo' => 'bar',
            'baz' => 42,
        ];

        $stream = Psr7::stream(\json_encode($array, \JSON_THROW_ON_ERROR));

        self::assertSame($array, Psr7::jsonBodyToArray($stream));
    }

    #[Test]
    public function jsonBodyToArrayHappySadPathInvalid(): void
    {
        $array = [
            'foo' => 'bar',
            'baz' => 42,
        ];

        $stream = Psr7::stream(\substr(\json_encode($array, \JSON_THROW_ON_ERROR), 0, -1));

        self::assertNull(Psr7::jsonBodyToArray($stream));
    }

    #[Test]
    public function jsonBodyToArrayHappySadPathNotArray(): void
    {
        $stream = Psr7::stream(\substr(\json_encode('false', \JSON_THROW_ON_ERROR), 0, -1));

        self::assertNull(Psr7::jsonBodyToArray($stream));
    }

    #[Test]
    public function expectsReturnsTrueWhenContentTypeMatchesAcceptHeader(): void
    {
        $message = $this->createMock(MessageInterface::class);
        $message->method('getHeaderLine')
            ->willReturnMap([
                ['Accept', 'application/json'],
                ['Content-Type', ''],
            ]);

        self::assertTrue(Psr7::expects($message, 'application/json'));
    }

    #[Test]
    public function expectsReturnsTrueWhenContentTypeMatchesContentTypeHeader(): void
    {
        $message = $this->createMock(MessageInterface::class);
        $message->method('getHeaderLine')
            ->willReturnMap([
                ['Accept', ''],
                ['Content-Type', 'application/json'],
            ]);

        self::assertTrue(Psr7::expects($message, 'application/json'));
    }

    #[Test]
    public function expectsReturnsTrueWhenContentTypeMatchesWithStructuredSyntaxSuffix(): void
    {
        $message = $this->createMock(MessageInterface::class);
        $message->method('getHeaderLine')
            ->willReturnMap([
                ['Accept', 'application/vnd.api+json'],
                ['Content-Type', ''],
            ]);

        self::assertTrue(Psr7::expects($message, 'application/json'));
    }

    #[Test]
    public function expectsReturnsFalseWhenNoMatchingContentType(): void
    {
        $message = $this->createMock(MessageInterface::class);
        $message->method('getHeaderLine')
            ->willReturnMap([
                ['Accept', 'text/html'],
                ['Content-Type', 'text/plain'],
            ]);

        self::assertFalse(Psr7::expects($message, 'application/json'));
    }

    #[Test]
    public function expectsReturnsFalseWhenHeadersAreEmpty(): void
    {
        $message = $this->createMock(MessageInterface::class);
        $message->method('getHeaderLine')
            ->willReturnMap([
                ['Accept', ''],
                ['Content-Type', ''],
            ]);

        self::assertFalse(Psr7::expects($message, 'application/json'));
    }

    #[Test]
    public function streamDefaultValueReturnsEmptyStream(): void
    {
        self::assertSame('', (string)Psr7::stream());
    }

    #[DataProvider('providesValidStreamTestCases')]
    #[Test]
    public function streamWillCastStringOrStringableToStream(string $expected, string|\Stringable $test): void
    {
        $stream = Psr7::stream($test);

        self::assertInstanceOf(StreamInterface::class, $stream);
        self::assertSame($expected, $stream->getContents());
    }

    public static function providesValidStreamTestCases(): \Generator
    {
        yield 'string' => ['Hello, World', 'Hello, World'];

        yield \Stringable::class => ['One Two Three', new class implements \Stringable {
            public function __toString(): string
            {
                return 'One Two Three';
            }
        },];

        yield '__toString' => ['Foo Bar Baz', new class {
            public function __toString(): string
            {
                return "Foo Bar Baz";
            }
        },];

        $test = 'StreamInterface Implements __toString';
        yield 'stream' => [$test, new StreamFactory()->createStream($test)];
    }

    #[Test]
    public function streamWillReturnPassedInstanceIfStreamInterface(): void
    {
        $stream = $this->createMock(StreamInterface::class);

        self::assertSame($stream, Psr7::stream($stream));
    }
}
