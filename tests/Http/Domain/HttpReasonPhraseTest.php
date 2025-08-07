<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Domain;

use PhoneBurner\Pinch\Component\Http\Domain\HttpReasonPhrase;
use PhoneBurner\Pinch\Component\Http\Domain\HttpStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(HttpReasonPhrase::class)]
final class HttpReasonPhraseTest extends TestCase
{
    #[Test]
    public function lookupReturnsCorrectPhraseForKnownCodes(): void
    {
        self::assertSame('OK', HttpReasonPhrase::lookup(HttpStatus::OK));
        self::assertSame('Not Found', HttpReasonPhrase::lookup(HttpStatus::NOT_FOUND));
        self::assertSame('Internal Server Error', HttpReasonPhrase::lookup(HttpStatus::INTERNAL_SERVER_ERROR));
        self::assertSame('I Am A Teapot', HttpReasonPhrase::lookup(HttpStatus::I_AM_A_TEAPOT));
    }

    #[Test]
    public function lookupReturnsEmptyStringForUnknownCode(): void
    {
        self::assertSame('', HttpReasonPhrase::lookup(999)); // Assuming 999 is not a standard code
    }

    #[Test]
    public function lookupReturnsEmptyStringForZeroCode(): void
    {
        self::assertSame('', HttpReasonPhrase::lookup(0));
    }
}
