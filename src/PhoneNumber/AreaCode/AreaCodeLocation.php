<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\PhoneNumber\AreaCode;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Component\I18n\Region\Region;
use PhoneBurner\Pinch\Component\I18n\Subdivision\SubdivisionCode;
use UnexpectedValueException;

/**
 * Collection of ISO3166-1 alpha-2 and ISO 3166-2 country/subdivision code strings
 * Also uses "NANP" to indicate an area code that is associated with all the
 * NANP regions, for example, a toll-free, or unassigned number.
 */
#[Contract]
final readonly class AreaCodeLocation
{
    public const string NANP = 'NANP';

    /**
     * United States Territories are listed as regions in the list of
     * NANP region codes for wider compatiblity, including the NANP database.
     *
     * @var array<value-of<Region>, Region>
     */
    public const array NANP_REGIONS = [
        Region::AI->value => Region::AI, // "Anguilla",
        Region::AG->value => Region::AG, // "Antigua & Barbuda",
        Region::BS->value => Region::BS, // "Bahamas",
        Region::BB->value => Region::BB, // "Barbados",
        Region::BM->value => Region::BM, // "Bermuda",
        Region::VG->value => Region::VG, // "British Virgin Islands",
        Region::CA->value => Region::CA, // "Canada",
        Region::KY->value => Region::KY, // "Cayman Islands",
        Region::DM->value => Region::DM, // "Dominica",
        Region::DO->value => Region::DO, // "Dominican Republic",
        Region::GD->value => Region::GD, // "Grenada",
        Region::JM->value => Region::JM, // "Jamaica",
        Region::MS->value => Region::MS, // "Montserrat",
        Region::SX->value => Region::SX, // "Sint Maarten",
        Region::KN->value => Region::KN, // "St. Kitts & Nevis",
        Region::LC->value => Region::LC, // "St. Lucia",
        Region::VC->value => Region::VC, // "St. Vincent & Grenadines",
        Region::TT->value => Region::TT, // "Trinidad & Tobago",
        Region::TC->value => Region::TC, // "Turks & Caicos Islands",
        Region::US->value => Region::US, // "United States",
        Region::VI->value => Region::VI, // "U.S. Virgin Islands",
        Region::AS->value => Region::AS, // "American Samoa",
        Region::PR->value => Region::PR, // "Puerto Rico",
        Region::GU->value => Region::GU, // "Guam",
        Region::MP->value => Region::MP, // "Northern Mariana Islands",
    ];

    private const array UNITED_STATES_TERRITORIES = [
        Region::AS->value => SubdivisionCode::US_AS, // "American Samoa",
        Region::GU->value => SubdivisionCode::US_GU, // "Guam",
        Region::MP->value => SubdivisionCode::US_MP, // "Northern Mariana Islands",
        Region::PR->value => SubdivisionCode::US_PR, // "Puerto Rico",
        Region::VI->value => SubdivisionCode::US_VI, // "U.S. Virgin Islands",
    ];

    /**
     * @phpstan-var value-of<Region>|self::NANP
     */
    public string $region;

    /**
     * @phpstan-var array<SubdivisionCode::*, SubdivisionCode::*>
     */
    public array $subdivisions;

    /**
     * @param array<string> $codes
     * @phpstan-assert array<SubdivisionCode::*|value-of<Region>|self::NANP> $codes
     */
    private function __construct(array $codes)
    {
        $regions = [];
        $subdivisions = [];
        foreach (\array_unique($codes) as $code) {
            if ($code === self::NANP) {
                $regions[] = self::NANP;
                continue;
            }

            $code = self::UNITED_STATES_TERRITORIES[$code] ?? $code;

            $region = \substr($code, 0, 2);
            if (! \array_key_exists($region, self::NANP_REGIONS)) {
                throw new UnexpectedValueException('Invalid NANP Region Code: ' . $region);
            }
            $regions[$region] = $region;

            if (\strlen($code) === 2) {
                continue;
            }

            if (! SubdivisionCode::validate($code)) {
                throw new UnexpectedValueException('Invalid/Undefined Subdivision Code: ' . $code);
            }
            $subdivisions[$code] = $code;
        }

        if (\count($regions) !== 1) {
            throw new \InvalidArgumentException('AreaCodeLocation Requires 1 Region');
        }

        $this->region = $regions[\array_key_first($regions)];
        $this->subdivisions = $subdivisions;
    }

    /**
     * @phpstan-param self::NANP|value-of<Region>|SubdivisionCode::* $codes
     */
    public static function make(string ...$codes): self
    {
        static $cache = [];
        \sort($codes);

        return $cache[\implode('&', $codes)] ??= new self($codes);
    }

    public static function NANP(): self
    {
        return self::make(self::NANP);
    }
}
