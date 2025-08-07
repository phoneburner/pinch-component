<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\PhoneNumber;

use PhoneBurner\Pinch\Component\PhoneNumber\NullPhoneNumber;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NullPhoneNumberTest extends TestCase
{
    #[Test]
    public function nullPhoneNumberRepresentsEmptyPhoneNumber(): void
    {
        $sut = NullPhoneNumber::make();
        self::assertNull($sut->toE164());
        self::assertSame($sut, $sut->getValue());
        self::assertSame($sut, NullPhoneNumber::make());
    }
}
