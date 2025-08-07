<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\I18n\Subdivision;

use PhoneBurner\Pinch\Component\I18n\IsoLocale;
use PhoneBurner\Pinch\Component\I18n\Subdivision\SubdivisionCode;
use PhoneBurner\Pinch\Component\I18n\Subdivision\SubdivisionName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

#[CoversClass(SubdivisionName::class)]
final class SubdivisionNameTest extends TestCase
{
    #[Test]
    public function constructorSetsProperties(): void
    {
        $name = new SubdivisionName('Test Name', IsoLocale::FR_CA);
        self::assertSame('Test Name', $name->value);
        self::assertSame(IsoLocale::FR_CA, $name->locale);
    }

    #[Test]
    public function constructorUsesDefaultLocale(): void
    {
        $name = new SubdivisionName('Default Locale Name');
        self::assertSame('Default Locale Name', $name->value);
        self::assertSame(IsoLocale::EN_US, $name->locale);
    }

    #[Test]
    public function toStringReturnsValue(): void
    {
        $name = new SubdivisionName('String Value');
        self::assertSame('String Value', (string)$name);
    }

    #[Test]
    public function allReturnsArrayOfNames(): void
    {
        $all = SubdivisionName::all();
        self::assertIsArray($all);
        self::assertNotEmpty($all);

        // Check structure and a few values
        self::assertArrayHasKey('US_CA', $all);
        self::assertSame('California', $all['US_CA']);
        self::assertArrayHasKey('CA_ON', $all);
        self::assertSame('Ontario', $all['CA_ON']);
    }

    #[Test]
    public function displayReturnsCorrectNameForValidCode(): void
    {
        self::assertSame('California', SubdivisionName::display(SubdivisionCode::US_CA));
        self::assertSame('Quebec', SubdivisionName::display(SubdivisionCode::CA_QC));
        self::assertSame('Texas', SubdivisionName::display('US-TX')); // Test with string input
    }

    #[Test]
    public function displayThrowsExceptionForInvalidCodeFormat(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid or Unsupported ISO3166-2 Subdivision Code');
        // @phpstan-ignore-next-line - Intentionally passing invalid type
        SubdivisionName::display('INVALID');
    }

    #[Test]
    public function displayThrowsExceptionForUnsupportedCode(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid or Unsupported ISO3166-2 Subdivision Code');
        // @phpstan-ignore-next-line - Intentionally passing invalid type
        SubdivisionName::display('GB-ENG'); // Valid format, but not in our const list
    }

    #[Test]
    public function shortReturnsCorrectShortCodeForValidCode(): void
    {
        self::assertSame('CA', SubdivisionName::short(SubdivisionCode::US_CA));
        self::assertSame('QC', SubdivisionName::short(SubdivisionCode::CA_QC));
        self::assertSame('TX', SubdivisionName::short('US-TX')); // Test with string input
    }

    #[Test]
    public function shortThrowsExceptionForInvalidCodeFormat(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid or Unsupported ISO3166-2 Subdivision Code');
        // @phpstan-ignore-next-line - Intentionally passing invalid type
        SubdivisionName::short('INVALID');
    }

    #[Test]
    public function shortThrowsExceptionForUnsupportedCode(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid or Unsupported ISO3166-2 Subdivision Code');
        // @phpstan-ignore-next-line - Intentionally passing invalid type
        SubdivisionName::short('GB-ENG'); // Valid format, but not in our const list
    }
}
