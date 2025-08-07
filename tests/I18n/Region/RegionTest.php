<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\I18n\Region;

use PhoneBurner\Pinch\Component\I18n\IsoLocale;
use PhoneBurner\Pinch\Component\I18n\Region\Region;
use PhoneBurner\Pinch\Component\I18n\Region\RegionAware;
use PhoneBurner\Pinch\Component\I18n\Region\RegionName;
use PhoneBurner\Pinch\Component\I18n\Subdivision\SubdivisionCode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

// For subdivision string test

#[CoversClass(Region::class)]
final class RegionTest extends TestCase
{
    #[Test]
    public function enumCasesHaveCorrectValue(): void
    {
        self::assertSame('US', Region::US->value);
        self::assertSame('CA', Region::CA->value);
        self::assertSame('GB', Region::GB->value);
    }

    #[Test]
    #[DataProvider('providesValidInstanceValues')]
    public function instanceReturnsCorrectRegionForValidInput(mixed $input, Region $expected): void
    {
        self::assertSame($expected, Region::instance($input));
    }

    public static function providesValidInstanceValues(): \Generator
    {
        yield 'Region enum case' => [Region::US, Region::US];
        yield 'RegionAware object' => [
            new class implements RegionAware
            {
                public function getRegion(): Region
                {
                    return Region::CA;
                }
            },
            Region::CA,
        ];
        yield 'Stringable object (Region code)' => [
            new readonly class ('GB') implements \Stringable
            {
                public function __construct(private string $v)
                {
                }

                public function __toString(): string
                {
                    return $this->v;
                }
            },
            Region::GB,
        ];
        yield 'Stringable object (Subdivision code)' => [
            new readonly class (SubdivisionCode::US_NY) implements \Stringable
            {
                public function __construct(private string $v)
                {
                }

                public function __toString(): string
                {
                    return $this->v;
                }
            },
            Region::US,
        ];
        yield '2-letter uppercase string' => ['DE', Region::DE];
        yield '2-letter lowercase string' => ['fr', Region::FR];
        yield 'Subdivision code string' => [SubdivisionCode::CA_ON, Region::CA];
        yield 'Subdivision code string lowercase' => ['ca-qc', Region::CA];
    }

    #[Test]
    #[DataProvider('providesInvalidInstanceValues')]
    public function instanceThrowsExceptionForInvalidInput(mixed $invalid_input): void
    {
        $this->expectException(UnexpectedValueException::class);
        Region::instance($invalid_input);
    }

    public static function providesInvalidInstanceValues(): \Generator
    {
        yield 'invalid 2-letter string' => ['XX'];
        yield 'invalid 3-letter string' => ['USA'];
        yield 'empty string' => [''];
        yield 'integer' => [123];
        yield 'boolean true' => [true];
        yield 'boolean false' => [false];
        yield 'array' => [[Region::US]];
        yield 'object without Stringable/RegionAware' => [new \stdClass()];
        yield 'invalid subdivision format string' => ['US-XYZ123'];
    }

    #[Test]
    #[DataProvider('providesValidInstanceValues')]
    public function castReturnsCorrectRegionForValidInput(mixed $input, Region $expected): void
    {
        // Use the same provider as instance()
        self::assertSame($expected, Region::cast($input));
    }

    #[Test]
    #[DataProvider('providesInvalidInstanceValues')]
    public function castReturnsNullForInvalidInput(mixed $invalid_input): void
    {
        // Use the same provider as instance()
        self::assertNull(Region::cast($invalid_input));
    }

    #[Test]
    public function castReturnsNullForNullInput(): void
    {
        self::assertNull(Region::cast(null));
    }

    #[Test]
    public function nameReturnsCorrectRegionNameForDefaultLocale(): void
    {
        $region = Region::US;
        $name = $region->name();
        self::assertInstanceOf(RegionName::class, $name);
        self::assertSame('United States', $name->value);
        self::assertSame(IsoLocale::EN_US, $name->locale);
    }

    #[Test]
    public function nameReturnsCorrectRegionNameForSpecificLocale(): void
    {
        // Assuming Region::MK has a specific MK_MK locale attribute
        $region = Region::MK;
        $name_mk = $region->name(IsoLocale::MK_MK);
        self::assertInstanceOf(RegionName::class, $name_mk);
        self::assertSame('Северна Македонија', $name_mk->value); // Check the specific name
        self::assertSame(IsoLocale::MK_MK, $name_mk->locale);

        // Check fallback to default if locale not found
        $name_default = $region->name(IsoLocale::FR_CA);
        self::assertInstanceOf(RegionName::class, $name_default);
        self::assertSame('North Macedonia', $name_default->value); // Default name
        self::assertSame(IsoLocale::EN_US, $name_default->locale); // Default locale
    }

    #[Test]
    public function getRegionReturnsSelf(): void
    {
        $region = Region::JP;
        self::assertSame($region, $region->getRegion());
    }
}
