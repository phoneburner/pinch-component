<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\PhoneNumber\AreaCode;

use PhoneBurner\Pinch\Component\PhoneNumber\AreaCode\AreaCode;
use PhoneBurner\Pinch\Component\PhoneNumber\AreaCode\AreaCodeAware;
use PhoneBurner\Pinch\Component\PhoneNumber\AreaCode\AreaCodePurpose;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AreaCodePurposeTest extends TestCase
{
    #[Test]
    public function allAreaCodesHaveAPurpose(): void
    {
        foreach (AreaCode::all() as $area_code) {
            self::assertInstanceOf(AreaCodePurpose::class, AreaCodePurpose::lookup($area_code));
        }
    }

    #[DataProvider('providesLookupTestCases')]
    #[Test]
    public function lookupReturnsExpectedEnumValue(AreaCode $area_code, AreaCodePurpose $expected): void
    {
        $area_code_aware = new readonly class ($area_code) implements AreaCodeAware {
            public function __construct(private AreaCode $area_code)
            {
            }

            public function getAreaCode(): AreaCode
            {
                return $this->area_code;
            }
        };

        self::assertSame($expected, AreaCodePurpose::lookup($area_code_aware));
    }

    public static function providesLookupTestCases(): \Generator
    {
        yield [AreaCode::make('314'), AreaCodePurpose::GeneralPurpose];
        yield [AreaCode::make('330'), AreaCodePurpose::GeneralPurpose];
        yield [AreaCode::make('216'), AreaCodePurpose::GeneralPurpose];
        yield [AreaCode::make('500'), AreaCodePurpose::PersonalCommunication];
        yield [AreaCode::make('521'), AreaCodePurpose::PersonalCommunication];
        yield [AreaCode::make('522'), AreaCodePurpose::PersonalCommunication];
        yield [AreaCode::make('523'), AreaCodePurpose::PersonalCommunication];
        yield [AreaCode::make('524'), AreaCodePurpose::PersonalCommunication];
        yield [AreaCode::make('525'), AreaCodePurpose::PersonalCommunication];
        yield [AreaCode::make('526'), AreaCodePurpose::PersonalCommunication];
        yield [AreaCode::make('527'), AreaCodePurpose::PersonalCommunication];
        yield [AreaCode::make('528'), AreaCodePurpose::PersonalCommunication];
        yield [AreaCode::make('529'), AreaCodePurpose::PersonalCommunication];
        yield [AreaCode::make('533'), AreaCodePurpose::PersonalCommunication];
        yield [AreaCode::make('544'), AreaCodePurpose::PersonalCommunication];
        yield [AreaCode::make('566'), AreaCodePurpose::PersonalCommunication];
        yield [AreaCode::make('577'), AreaCodePurpose::PersonalCommunication];
        yield [AreaCode::make('588'), AreaCodePurpose::PersonalCommunication];
        yield [AreaCode::make('600'), AreaCodePurpose::CanadianNonGeographicTariffed];
        yield [AreaCode::make('622'), AreaCodePurpose::CanadianNonGeographic];
        yield [AreaCode::make('700'), AreaCodePurpose::InterexchangeCarrier];
        yield [AreaCode::make('710'), AreaCodePurpose::Government];
        yield [AreaCode::make('800'), AreaCodePurpose::TollFree];
        yield [AreaCode::make('833'), AreaCodePurpose::TollFree];
        yield [AreaCode::make('844'), AreaCodePurpose::TollFree];
        yield [AreaCode::make('855'), AreaCodePurpose::TollFree];
        yield [AreaCode::make('866'), AreaCodePurpose::TollFree];
        yield [AreaCode::make('877'), AreaCodePurpose::TollFree];
        yield [AreaCode::make('888'), AreaCodePurpose::TollFree];
        yield [AreaCode::make('900'), AreaCodePurpose::PremiumService];
    }
}
