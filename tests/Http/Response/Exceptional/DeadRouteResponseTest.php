<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Response\Exceptional;

use PhoneBurner\Pinch\Component\Http\Domain\HttpHeader;
use PhoneBurner\Pinch\Component\Http\Domain\HttpStatus;
use PhoneBurner\Pinch\Component\Http\Response\Exceptional\DeadRouteResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DeadRouteResponseTest extends TestCase
{
    #[Test]
    public function responseHasExpectedDefaults(): void
    {
        $sut = new DeadRouteResponse();

        self::assertSame(HttpStatus::GONE, $sut->getStatusCode());
        self::assertSame('No Longer Supported', $sut->getStatusTitle());
        self::assertSame('The functionality formerly provided at this address is no longer supported.', $sut->getStatusDetail());
        self::assertSame([], $sut->getAdditional());

        self::assertSame(HttpStatus::GONE, $sut->getCode());
        self::assertSame('HTTP 410: Gone', $sut->getMessage());

        self::assertSame('text/plain; charset=utf-8', $sut->getHeaderLine(HttpHeader::CONTENT_TYPE));
        self::assertSame('HTTP 410: Gone', $sut->getBody()->getContents());
    }
}
