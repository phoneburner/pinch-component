<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Response\Exceptional;

use PhoneBurner\Pinch\Component\Http\Domain\HttpHeader;
use PhoneBurner\Pinch\Component\Http\Domain\HttpReasonPhrase;
use PhoneBurner\Pinch\Component\Http\Domain\HttpStatus;
use PhoneBurner\Pinch\Component\Http\Response\Exceptional\NotImplementedResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NotImplementedResponseTest extends TestCase
{
    #[Test]
    public function responseHasExpectedDefaults(): void
    {
        $sut = new NotImplementedResponse();

        self::assertSame(HttpStatus::NOT_IMPLEMENTED, $sut->getStatusCode());
        self::assertSame(HttpReasonPhrase::NOT_IMPLEMENTED, $sut->getStatusTitle());
        self::assertSame('This functionality is not yet implemented.', $sut->getStatusDetail());
        self::assertSame([], $sut->getAdditional());

        self::assertSame(HttpStatus::NOT_IMPLEMENTED, $sut->getCode());
        self::assertSame('HTTP 501: Not Implemented', $sut->getMessage());

        self::assertSame('text/plain; charset=utf-8', $sut->getHeaderLine(HttpHeader::CONTENT_TYPE));
        self::assertSame('HTTP 501: Not Implemented', $sut->getBody()->getContents());
    }
}
