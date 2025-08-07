<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Response\Exceptional;

use PhoneBurner\Pinch\Component\Http\Domain\HttpHeader;
use PhoneBurner\Pinch\Component\Http\Domain\HttpReasonPhrase;
use PhoneBurner\Pinch\Component\Http\Domain\HttpStatus;
use PhoneBurner\Pinch\Component\Http\Response\Exceptional\ServerErrorResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ServerErrorResponseTest extends TestCase
{
    #[Test]
    public function responseHasExpectedDefaults(): void
    {
        $sut = new ServerErrorResponse();

        self::assertSame(HttpStatus::INTERNAL_SERVER_ERROR, $sut->getStatusCode());
        self::assertSame(HttpReasonPhrase::INTERNAL_SERVER_ERROR, $sut->getStatusTitle());
        self::assertSame('An internal server error occurred.', $sut->getStatusDetail());
        self::assertSame([], $sut->getAdditional());

        self::assertSame(HttpStatus::INTERNAL_SERVER_ERROR, $sut->getCode());
        self::assertSame('HTTP 500: Internal Server Error', $sut->getMessage());

        self::assertSame('text/plain; charset=utf-8', $sut->getHeaderLine(HttpHeader::CONTENT_TYPE));
        self::assertSame('HTTP 500: Internal Server Error', $sut->getBody()->getContents());
    }
}
