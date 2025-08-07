<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\PhoneNumber\AreaCode;

use PhoneBurner\Pinch\Component\I18n\Region\Region;
use PhoneBurner\Pinch\Component\I18n\Subdivision\SubdivisionCode;
use PhoneBurner\Pinch\Component\PhoneNumber\AreaCode\AreaCodeLocation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AreaCodeLocationTest extends TestCase
{
    #[DataProvider('providesHappyPathTestCases')]
    #[Test]
    public function makeHappyPath(array $input, string $region, array $subdivisions): void
    {
        $sut = AreaCodeLocation::make(...$input);

        self::assertSame($region, $sut->region);
        self::assertSame($subdivisions, $sut->subdivisions);
        self::assertSame($sut, AreaCodeLocation::make(...$input));
        self::assertSame(AreaCodeLocation::NANP(), AreaCodeLocation::make(AreaCodeLocation::NANP));
    }

    public static function providesHappyPathTestCases(): \Generator
    {
        yield [[AreaCodeLocation::NANP], AreaCodeLocation::NANP, []];

        yield [[Region::AI->value], Region::AI->value, []]; // "Anguilla",
        yield [[Region::AG->value], Region::AG->value, []]; // "Antigua & Barbuda",
        yield [[Region::BS->value], Region::BS->value, []]; // "Bahamas",
        yield [[Region::BB->value], Region::BB->value, []]; // "Barbados",
        yield [[Region::BM->value], Region::BM->value, []]; // "Bermuda",
        yield [[Region::VG->value], Region::VG->value, []]; // "British Virgin Islands",
        yield [[Region::CA->value], Region::CA->value, []]; // "Canada",
        yield [[Region::KY->value], Region::KY->value, []]; // "Cayman Islands",
        yield [[Region::DM->value], Region::DM->value, []]; // "Dominica",
        yield [[Region::DO->value], Region::DO->value, []]; // "Dominican Republic",
        yield [[Region::GD->value], Region::GD->value, []]; // "Grenada",
        yield [[Region::JM->value], Region::JM->value, []]; // "Jamaica",
        yield [[Region::MS->value], Region::MS->value, []]; // "Montserrat",
        yield [[Region::SX->value], Region::SX->value, []]; // "Sint Maarten",
        yield [[Region::KN->value], Region::KN->value, []]; // "St. Kitts & Nevis",
        yield [[Region::LC->value], Region::LC->value, []]; // "St. Lucia",
        yield [[Region::VC->value], Region::VC->value, []]; // "St. Vincent & Grenadines",
        yield [[Region::TT->value], Region::TT->value, []]; // "Trinidad & Tobago",
        yield [[Region::TC->value], Region::TC->value, []]; // "Turks & Caicos Islands",
        yield [[Region::US->value], Region::US->value, []]; // "United States",

        // Passing the same region twice is ok.
        yield [[Region::US->value, Region::US->value], Region::US->value, []];
        yield [[AreaCodeLocation::NANP, AreaCodeLocation::NANP, AreaCodeLocation::NANP], AreaCodeLocation::NANP, []];

        yield [[SubdivisionCode::US_MO], Region::US->value, [SubdivisionCode::US_MO => SubdivisionCode::US_MO]];
        yield [[SubdivisionCode::US_MO, Region::US->value], Region::US->value, [SubdivisionCode::US_MO => SubdivisionCode::US_MO]];
        yield [[Region::US->value, SubdivisionCode::US_MO,], Region::US->value, [SubdivisionCode::US_MO => SubdivisionCode::US_MO]];
        yield [[SubdivisionCode::US_MO, SubdivisionCode::US_MO,], Region::US->value, [SubdivisionCode::US_MO => SubdivisionCode::US_MO]];

        yield [
            [SubdivisionCode::US_MO, SubdivisionCode::US_OH, SubdivisionCode::US_MO],
            Region::US->value,
            [
                SubdivisionCode::US_MO => SubdivisionCode::US_MO,
                SubdivisionCode::US_OH => SubdivisionCode::US_OH,
            ],
        ];

        yield [
            [SubdivisionCode::US_OH, SubdivisionCode::US_MO],
            Region::US->value,
            [
                SubdivisionCode::US_MO => SubdivisionCode::US_MO,
                SubdivisionCode::US_OH => SubdivisionCode::US_OH,
            ],
        ];

        yield [[SubdivisionCode::CA_NL], Region::CA->value, [SubdivisionCode::CA_NL => SubdivisionCode::CA_NL]];

        yield [
            [SubdivisionCode::CA_NS, SubdivisionCode::CA_PE],
            Region::CA->value,
            [
                SubdivisionCode::CA_NS => SubdivisionCode::CA_NS,
                SubdivisionCode::CA_PE => SubdivisionCode::CA_PE,
            ],
        ];

        yield [[Region::AS->value], Region::US->value, [SubdivisionCode::US_AS => SubdivisionCode::US_AS]];
        yield [[Region::GU->value], Region::US->value, [SubdivisionCode::US_GU => SubdivisionCode::US_GU]];
        yield [[Region::MP->value], Region::US->value, [SubdivisionCode::US_MP => SubdivisionCode::US_MP]];
        yield [[Region::PR->value], Region::US->value, [SubdivisionCode::US_PR => SubdivisionCode::US_PR]];
        yield [[Region::VI->value], Region::US->value, [SubdivisionCode::US_VI => SubdivisionCode::US_VI]];

        yield [[SubdivisionCode::US_AS], Region::US->value, [SubdivisionCode::US_AS => SubdivisionCode::US_AS]];
        yield [[SubdivisionCode::US_GU], Region::US->value, [SubdivisionCode::US_GU => SubdivisionCode::US_GU]];
        yield [[SubdivisionCode::US_MP], Region::US->value, [SubdivisionCode::US_MP => SubdivisionCode::US_MP]];
        yield [[SubdivisionCode::US_PR], Region::US->value, [SubdivisionCode::US_PR => SubdivisionCode::US_PR]];
        yield [[SubdivisionCode::US_VI], Region::US->value, [SubdivisionCode::US_VI => SubdivisionCode::US_VI]];

        // Usually passing more than one region code would result in an exception
        // being thrown; however, US Territories are a special case and are
        // cast to their subdivision equivalent.
        yield [
            [
                Region::AS->value,
                Region::GU->value,
                Region::MP->value,
                Region::PR->value,
                Region::VI->value,
            ],
            Region::US->value,
            [
                SubdivisionCode::US_AS => SubdivisionCode::US_AS,
                SubdivisionCode::US_GU => SubdivisionCode::US_GU,
                SubdivisionCode::US_MP => SubdivisionCode::US_MP,
                SubdivisionCode::US_PR => SubdivisionCode::US_PR,
                SubdivisionCode::US_VI => SubdivisionCode::US_VI,
            ],
        ];
    }

    #[DataProvider('providesDifferentRegionSadPathTestCases')]
    #[Test]
    public function passingTwoDifferentRegionsFails(array $input): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('AreaCodeLocation Requires 1 Region');
        AreaCodeLocation::make(...$input);
    }

    public static function providesDifferentRegionSadPathTestCases(): \Generator
    {
        yield [[Region::US->value, Region::CA->value]];
        yield [[SubdivisionCode::US_CA, SubdivisionCode::CA_ON]];
        yield [[SubdivisionCode::US_CA, Region::CA->value]];
    }

    #[Test]
    public function passingInvalidRegionCodeFails(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid NANP Region Code: MK');
        AreaCodeLocation::make(Region::MK->value);
    }

    #[Test]
    public function passingInvalidSubdivisionCodeFails(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Undefined Subdivision Code: US-PE');
        /** @phpstan-ignore-next-line intentional defect */
        AreaCodeLocation::make('US-PE');
    }
}
