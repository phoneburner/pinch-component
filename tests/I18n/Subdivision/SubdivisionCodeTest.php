<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\I18n\Subdivision;

use PhoneBurner\Pinch\Component\I18n\Region\Region;
use PhoneBurner\Pinch\Component\I18n\Region\RegionAware;
use PhoneBurner\Pinch\Component\I18n\Subdivision\SubdivisionCode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

#[CoversClass(SubdivisionCode::class)]
final class SubdivisionCodeTest extends TestCase
{
    #[Test]
    public function constructorThrowsExceptionForInvalidCode(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid Subdivision Code: INVALID-CODE');
        new SubdivisionCode('INVALID-CODE');
    }

    #[Test]
    public function constructorAcceptsValidCode(): void
    {
        $code = new SubdivisionCode(SubdivisionCode::US_CA);
        self::assertSame(SubdivisionCode::US_CA, $code->value);
    }

    #[Test]
    public function toStringReturnsValue(): void
    {
        $code = new SubdivisionCode(SubdivisionCode::CA_ON);
        self::assertSame(SubdivisionCode::CA_ON, (string)$code);
    }

    #[Test]
    public function allReturnsArrayOfSubdivisionCodeObjects(): void
    {
        $all = SubdivisionCode::all();
        self::assertIsArray($all);
        self::assertNotEmpty($all);

        // Check if keys are constant names and values are SubdivisionCode objects
        foreach ($all as $key => $code) {
            self::assertIsString($key);
            self::assertInstanceOf(SubdivisionCode::class, $code);
            // Assert constant value matches the object's value property
            self::assertSame(SubdivisionCode::{$key}, $code->value);
        }

        // Spot check a few
        self::assertArrayHasKey('US_NY', $all);
        self::assertSame(SubdivisionCode::US_NY, $all['US_NY']->value);
        self::assertArrayHasKey('CA_BC', $all);
        self::assertSame(SubdivisionCode::CA_BC, $all['CA_BC']->value);
    }

    #[Test]
    public function regionReturnsFilteredCodesForRegionObject(): void
    {
        $us_codes = SubdivisionCode::region(Region::US);
        self::assertIsArray($us_codes);
        self::assertNotEmpty($us_codes);
        self::assertArrayHasKey('US_CA', $us_codes); // Constant name as key
        self::assertSame(SubdivisionCode::US_CA, $us_codes['US_CA']); // Constant value as value
        self::assertArrayNotHasKey('CA_ON', $us_codes);

        foreach ($us_codes as $code) {
            self::assertStringStartsWith('US-', $code);
        }
    }

    #[Test]
    public function regionReturnsFilteredCodesForRegionAwareObject(): void
    {
        $region_aware = new class implements RegionAware {
            public function getRegion(): Region
            {
                return Region::CA;
            }
        };

        $ca_codes = SubdivisionCode::region($region_aware);
        self::assertIsArray($ca_codes);
        self::assertNotEmpty($ca_codes);
        self::assertArrayHasKey('CA_BC', $ca_codes);
        self::assertSame(SubdivisionCode::CA_BC, $ca_codes['CA_BC']);
        self::assertArrayNotHasKey('US_TX', $ca_codes);

        foreach ($ca_codes as $code) {
            self::assertStringStartsWith('CA-', $code);
        }
    }

    #[Test]
    public function regionReturnsFilteredCodesForString(): void
    {
        $us_codes = SubdivisionCode::region('US');
        self::assertIsArray($us_codes);
        self::assertNotEmpty($us_codes);
        self::assertArrayHasKey('US_FL', $us_codes);
        self::assertSame(SubdivisionCode::US_FL, $us_codes['US_FL']);
        self::assertArrayNotHasKey('CA_YT', $us_codes);
    }

    #[Test]
    public function regionReturnsEmptyArrayForUnknownString(): void
    {
        // Region::instance throws UnexpectedValueException for invalid region string
        $this->expectException(UnexpectedValueException::class);
        // @phpstan-ignore-next-line - Intentionally passing invalid type
        SubdivisionCode::region('INVALID');
    }

    #[Test]
    public function validateReturnsTrueForValidCode(): void
    {
        self::assertTrue(SubdivisionCode::validate(SubdivisionCode::US_TX));
        self::assertTrue(SubdivisionCode::validate(SubdivisionCode::CA_MB));
    }

    #[Test]
    public function validateReturnsFalseForInvalidCode(): void
    {
        // @phpstan-ignore-next-line - Intentionally checking invalid code
        self::assertFalse(SubdivisionCode::validate('INVALID-CODE'));
        // @phpstan-ignore-next-line - Intentionally checking invalid code
        self::assertFalse(SubdivisionCode::validate('US-XX'));
        // @phpstan-ignore-next-line - Intentionally checking invalid code
        self::assertFalse(SubdivisionCode::validate('ca-on')); // Case-sensitive
    }
}
