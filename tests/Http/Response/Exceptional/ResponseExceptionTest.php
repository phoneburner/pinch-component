<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Response\Exceptional;

use PhoneBurner\Pinch\Component\Http\Domain\ContentType;
use PhoneBurner\Pinch\Component\Http\Domain\HttpHeader;
use PhoneBurner\Pinch\Component\Http\Domain\HttpStatus;
use PhoneBurner\Pinch\Component\Http\Response\Exceptional\ResponseException;
use PhoneBurner\Pinch\Component\Http\Response\HtmlResponse;
use PhoneBurner\Pinch\Component\Http\Response\JsonResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ResponseExceptionTest extends TestCase
{
    #[Test]
    public function responseHasExpectedDefaults(): void
    {
        $response = new HtmlResponse('Hello, World');

        $sut = new ResponseException($response);

        self::assertSame(HttpStatus::OK, $sut->getStatusCode());
        self::assertSame('text/html; charset=utf-8', $sut->getHeaderLine(HttpHeader::CONTENT_TYPE));
        self::assertSame('Hello, World', $sut->getBody()->getContents());
        self::assertNull($sut->getPrevious());
        self::assertSame('', $sut->getMessage());
        self::assertSame(HttpStatus::OK, $sut->getCode());
        self::assertSame($response, $sut->getWrapped());
    }

    #[Test]
    public function responseCanReturnResponseWithExceptionMessageAndPrevious(): void
    {
        $previous = new \RuntimeException('Test');
        $response = new JsonResponse(['message' => 'Hello, World'], 444);

        $sut = new ResponseException($response, 'Test Message', $previous);

        self::assertSame(444, $sut->getStatusCode());
        self::assertSame(ContentType::JSON, $sut->getHeaderLine(HttpHeader::CONTENT_TYPE));
        self::assertSame('{"message":"Hello, World"}', $sut->getBody()->getContents());
        self::assertSame($previous, $sut->getPrevious());
        self::assertSame('Test Message', $sut->getMessage());
        self::assertSame(444, $sut->getCode());
        self::assertSame($response, $sut->getWrapped());
    }

    #[Test]
    public function responseHasCanMutate(): void
    {
        $response = new HtmlResponse('Hello, World');

        $sut = new ResponseException($response);

        $sut->withAddedHeader(HttpHeader::X_RATELIMIT_LIMIT, '1000');

        self::assertSame(HttpStatus::OK, $sut->getStatusCode());
        self::assertSame('text/html; charset=utf-8', $sut->getHeaderLine(HttpHeader::CONTENT_TYPE));
        self::assertSame('Hello, World', $sut->getBody()->getContents());
        self::assertNull($sut->getPrevious());
        self::assertSame('', $sut->getMessage());
        self::assertSame(HttpStatus::OK, $sut->getCode());
        self::assertNotSame($response, $sut->getWrapped());
        self::assertSame('1000', $sut->getHeaderLine(HttpHeader::X_RATELIMIT_LIMIT));
    }
}
