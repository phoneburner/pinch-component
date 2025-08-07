<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Response\Exceptional;

use PhoneBurner\Pinch\Component\Http\Domain\HttpHeader;
use PhoneBurner\Pinch\Component\Http\Domain\HttpStatus;
use PhoneBurner\Pinch\Component\Http\Response\Exceptional\PageNotFoundResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PageNotFoundResponseTest extends TestCase
{
    #[Test]
    public function responseHasExpectedDefaults(): void
    {
        $sut = new PageNotFoundResponse();

        self::assertSame(HttpStatus::NOT_FOUND, $sut->getStatusCode());
        self::assertSame('Page Not Found', $sut->getStatusTitle());
        self::assertSame('The requested page could not be found.', $sut->getStatusDetail());
        self::assertSame([], $sut->getAdditional());

        self::assertSame(HttpStatus::NOT_FOUND, $sut->getCode());
        self::assertSame('HTTP 404: Not Found', $sut->getMessage());

        self::assertSame('text/plain; charset=utf-8', $sut->getHeaderLine(HttpHeader::CONTENT_TYPE));
        self::assertSame('HTTP 404: Not Found', $sut->getBody()->getContents());
    }
}
