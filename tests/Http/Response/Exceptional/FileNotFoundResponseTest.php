<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Response\Exceptional;

use PhoneBurner\Pinch\Component\Http\Domain\HttpHeader;
use PhoneBurner\Pinch\Component\Http\Domain\HttpStatus;
use PhoneBurner\Pinch\Component\Http\Response\Exceptional\FileNotFoundResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FileNotFoundResponseTest extends TestCase
{
    #[Test]
    public function responseHasExpectedDefaults(): void
    {
        $sut = new FileNotFoundResponse();

        self::assertSame(HttpStatus::NOT_FOUND, $sut->getStatusCode());
        self::assertSame('File Not Found', $sut->getStatusTitle());
        self::assertSame('The file requested could not be found.', $sut->getStatusDetail());
        self::assertSame([], $sut->getAdditional());

        self::assertSame(HttpStatus::NOT_FOUND, $sut->getCode());
        self::assertSame('HTTP 404: Not Found', $sut->getMessage());

        self::assertSame('text/plain; charset=utf-8', $sut->getHeaderLine(HttpHeader::CONTENT_TYPE));
        self::assertSame('HTTP 404: Not Found', $sut->getBody()->getContents());
    }
}
