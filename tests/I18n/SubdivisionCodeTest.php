<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\I18n;

use PhoneBurner\Pinch\Component\I18n\Region\Region;
use PhoneBurner\Pinch\Component\I18n\Subdivision\SubdivisionCode;
use PhoneBurner\Pinch\Component\I18n\Subdivision\SubdivisionName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

use function PhoneBurner\Pinch\Array\array_wrap;

final class SubdivisionCodeTest extends TestCase
{
    #[Test]
    public function regionCodesAreUniqueAndNonEmpty(): void
    {
        $codes = new \ReflectionClass(SubdivisionCode::class)->getConstants();
        self::assertNotEmpty($codes);
        self::assertCount(\count($codes), \array_unique($codes));
        foreach ($codes as $key => $code) {
            self::assertIsString($key);
            self::assertIsString($code);
            self::assertMatchesRegularExpression('/^[A-Z]{2}-[A-Z]{2}$/', $code);
            self::assertMatchesRegularExpression('/^[A-Z]{2}_[A-Z]{2}$/', $key);
            self::assertSame(\substr($code, 0, 2), \substr($key, 0, 2));
            self::assertSame(\substr($code, 3, 2), \substr($key, 3, 2));
            self::assertTrue(\defined(SubdivisionName::class . '::' . $key));
        }
    }

    #[DataProvider('providesSubdivisionCodes')]
    #[Test]
    public function validateReturnsTrueForValidSubdivisionCodes(string $subdivision_code): void
    {
        self::assertTrue(SubdivisionCode::validate($subdivision_code));
        /** @phpstan-ignore-next-line staticMethod.impossibleType Intentional defect for testing */
        self::assertFalse(SubdivisionCode::validate(\strtolower($subdivision_code)));
    }

    #[TestWith(['XX'])]
    #[TestWith(['xx'])]
    #[TestWith(['USA'])]
    #[TestWith([''])]
    #[TestWith(['US'])]
    #[TestWith(['US-XX'])]
    #[TestWith(['US_OH'])]
    #[TestWith(['CA'])]
    #[TestWith(['CA-XX'])]
    #[Test]
    public function validateReturnsFalseForInvalidSubdivisionCodes(string $subdivision_code): void
    {
        /** @phpstan-ignore-next-line intentional defect for testing */
        self::assertFalse(SubdivisionCode::validate($subdivision_code));
        /** @phpstan-ignore-next-line intentional defect for testing */
        self::assertFalse(SubdivisionCode::validate(\strtolower($subdivision_code)));
    }

    public static function providesSubdivisionCodes(): \Generator
    {
        yield from \array_map(array_wrap(...), \array_column(SubdivisionCode::all(), 'value'));
    }

    #[Test]
    public function regionReturnsExpectedCodes(): void
    {
        $subdivisions = SubdivisionCode::region(Region::CA->value);

        self::assertSame([
            'CA_AB' => 'CA-AB',
            'CA_BC' => 'CA-BC',
            'CA_MB' => 'CA-MB',
            'CA_NB' => 'CA-NB',
            'CA_NL' => 'CA-NL',
            'CA_NS' => 'CA-NS',
            'CA_NT' => 'CA-NT',
            'CA_NU' => 'CA-NU',
            'CA_ON' => 'CA-ON',
            'CA_PE' => 'CA-PE',
            'CA_QC' => 'CA-QC',
            'CA_SK' => 'CA-SK',
            'CA_YT' => 'CA-YT',
        ], $subdivisions);

        foreach ($subdivisions as $subdivision_code) {
            self::assertTrue(SubdivisionCode::validate($subdivision_code));
            self::assertSame($subdivisions, SubdivisionCode::region($subdivision_code));
        }
    }
}
