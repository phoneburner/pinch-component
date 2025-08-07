<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\I18n;

use PhoneBurner\Pinch\Component\I18n\IsoLocale;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(IsoLocale::class)]
final class IsoLocaleTest extends TestCase
{
    #[Test]
    public function enumCasesExistAndHaveCorrectValues(): void
    {
        // Test a few sample cases
        self::assertSame('en-US', IsoLocale::EN_US->value);
        self::assertSame('fr-CA', IsoLocale::FR_CA->value);
        self::assertSame('de-DE', IsoLocale::DE_DE->value);
        self::assertCount(377, IsoLocale::cases()); // Update count to actual 377
    }
}
