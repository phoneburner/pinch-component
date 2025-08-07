<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\I18n\Region;

use PhoneBurner\Pinch\Component\I18n\IsoLocale;
use PhoneBurner\Pinch\Component\I18n\Region\RegionName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RegionName::class)]
final class RegionNameTest extends TestCase
{
    #[Test]
    public function constructorSetsProperties(): void
    {
        $name = new RegionName('Test Region Name', IsoLocale::FR_CA);
        self::assertSame('Test Region Name', $name->value);
        self::assertSame(IsoLocale::FR_CA, $name->locale);
    }

    #[Test]
    public function constructorUsesDefaultLocale(): void
    {
        $name = new RegionName('Default Locale Region Name');
        self::assertSame('Default Locale Region Name', $name->value);
        self::assertSame(IsoLocale::EN_US, $name->locale);
    }

    #[Test]
    public function toStringReturnsValue(): void
    {
        $name = new RegionName('String Value Region');
        self::assertSame('String Value Region', (string)$name);
    }

    #[Test]
    public function constantsMapCorrectly(): void
    {
        // Verify a few constant values
        self::assertSame('United States', RegionName::US);
        self::assertSame('Canada', RegionName::CA);
        self::assertSame('United Kingdom', RegionName::GB);
        self::assertSame('Mexico', RegionName::MX);
    }
}
