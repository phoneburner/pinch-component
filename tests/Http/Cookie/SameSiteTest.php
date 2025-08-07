<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Cookie;

use PhoneBurner\Pinch\Component\Http\Cookie\SameSite;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SameSiteTest extends TestCase
{
    #[Test]
    public function itHasExpectedCases(): void
    {
        self::assertSame('Lax', SameSite::Lax->name);
        self::assertSame('Strict', SameSite::Strict->name);
        self::assertSame('None', SameSite::None->name);

        // Ensure there are only three cases
        self::assertCount(3, SameSite::cases());
    }
}
