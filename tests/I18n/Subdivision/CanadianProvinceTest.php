<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\I18n\Subdivision;

use PhoneBurner\Pinch\Component\I18n\IsoLocale;
use PhoneBurner\Pinch\Component\I18n\Region\Region;
use PhoneBurner\Pinch\Component\I18n\Subdivision\CanadianProvince;
use PhoneBurner\Pinch\Component\I18n\Subdivision\SubdivisionCode;
use PhoneBurner\Pinch\Component\I18n\Subdivision\SubdivisionName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CanadianProvince::class)]
final class CanadianProvinceTest extends TestCase
{
    #[Test]
    public function enumCasesHaveCorrectValue(): void
    {
        self::assertSame('ON', CanadianProvince::ON->value);
        self::assertSame('QC', CanadianProvince::QC->value);
    }

    #[Test]
    public function labelReturnsCorrectSubdivisionName(): void
    {
        $province = CanadianProvince::BC;
        $label = $province->label();

        self::assertInstanceOf(SubdivisionName::class, $label);
        self::assertSame('British Columbia', $label->value);
        self::assertSame(IsoLocale::EN_US, $label->locale); // Assuming default locale from attribute definition
    }

    #[Test]
    public function codeReturnsCorrectSubdivisionCode(): void
    {
        $province = CanadianProvince::AB;
        $code = $province->code();

        self::assertInstanceOf(SubdivisionCode::class, $code);
        self::assertSame(SubdivisionCode::CA_AB, $code->value);
    }

    #[Test]
    public function getRegionReturnsCanada(): void
    {
        self::assertSame(Region::CA, CanadianProvince::SK->getRegion());
    }

    #[Test]
    public function allCasesHaveLabelAndCode(): void
    {
        foreach (CanadianProvince::cases() as $case) {
            self::assertInstanceOf(SubdivisionName::class, $case->label());
            self::assertInstanceOf(SubdivisionCode::class, $case->code());
            self::assertSame(Region::CA, $case->getRegion());
        }
    }

    #[Test]
    #[DataProvider('validParsableValuesDataProvider')]
    public function instanceReturnsExpectedValue(mixed $value, CanadianProvince $state): void
    {
        self::assertSame($state, CanadianProvince::instance($value));
    }

    #[Test]
    #[DataProvider('invalidParsableValuesDataProvider')]
    public function instanceThrowsExpectedExceptionForInvalidInput(mixed $value): void
    {
        $this->expectException(\UnexpectedValueException::class);
        CanadianProvince::instance($value);
    }

    #[Test]
    #[DataProvider('validParsableValuesDataProvider')]
    public function parseReturnsExpectedValueForValidInput(mixed $value, CanadianProvince $state): void
    {
        self::assertSame($state, CanadianProvince::parse($value));
    }

    #[Test]
    #[DataProvider('invalidParsableValuesDataProvider')]
    public function parseReturnsExpectedValueForInvalidInput(mixed $value): void
    {
        self::assertNull(CanadianProvince::parse($value));
    }

    public static function validParsableValuesDataProvider(): \Generator
    {
        foreach (CanadianProvince::cases() as $province) {
            yield [$province, $province];
            yield [$province->value, $province];
            yield [$province->code()->value, $province];
            yield [\strtolower($province->code()->value), $province];
            yield [$province->label()->value, $province];
            yield [\strtoupper($province->label()->value), $province];
            yield [\strtolower($province->label()->value), $province];
        }

        yield ['PEI', CanadianProvince::PE];
        yield ['P.E.I.', CanadianProvince::PE];
        yield ['pei', CanadianProvince::PE];
        yield ['Newfoundland', CanadianProvince::NL];
        yield ['Newfoundland/Labrador', CanadianProvince::NL];
        yield ['Labrador', CanadianProvince::NL];
        yield ['B.C.', CanadianProvince::BC];
    }

    public static function invalidParsableValuesDataProvider(): \Generator
    {
        yield from [
            [null],
            [''],
            ['Invalid State'],
            ['XX'],
            [123],
            [new \stdClass()],
            [SubdivisionCode::US_AK],
            ["Ohio"],
        ];
    }
}
