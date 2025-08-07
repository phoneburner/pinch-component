<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\I18n\Subdivision;

use PhoneBurner\Pinch\Component\I18n\Region\Region;
use PhoneBurner\Pinch\Component\I18n\Region\RegionAware;

use function PhoneBurner\Pinch\Enum\case_attr_fetch;

enum CanadianProvince: string implements RegionAware
{
    #[SubdivisionName('Alberta')]
    #[SubdivisionCode(SubdivisionCode::CA_AB)]
    case AB = 'AB';

    #[SubdivisionName('British Columbia')]
    #[SubdivisionCode(SubdivisionCode::CA_BC)]
    case BC = 'BC';

    #[SubdivisionName('Manitoba')]
    #[SubdivisionCode(SubdivisionCode::CA_MB)]
    case MB = 'MB';

    #[SubdivisionName('New Brunswick')]
    #[SubdivisionCode(SubdivisionCode::CA_NB)]
    case NB = 'NB';

    #[SubdivisionName('Newfoundland and Labrador')]
    #[SubdivisionCode(SubdivisionCode::CA_NL)]
    case NL = 'NL';

    #[SubdivisionName('Nova Scotia')]
    #[SubdivisionCode(SubdivisionCode::CA_NS)]
    case NS = 'NS';

    #[SubdivisionName('Northwest Territories')]
    #[SubdivisionCode(SubdivisionCode::CA_NT)]
    case NT = 'NT';

    #[SubdivisionName('Nunavut')]
    #[SubdivisionCode(SubdivisionCode::CA_NU)]
    case NU = 'NU';

    #[SubdivisionName('Ontario')]
    #[SubdivisionCode(SubdivisionCode::CA_ON)]
    case ON = 'ON';

    #[SubdivisionName('Prince Edward Island')]
    #[SubdivisionCode(SubdivisionCode::CA_PE)]
    case PE = 'PE';

    #[SubdivisionName('Quebec')]
    #[SubdivisionCode(SubdivisionCode::CA_QC)]
    case QC = 'QC';

    #[SubdivisionName('Saskatchewan')]
    #[SubdivisionCode(SubdivisionCode::CA_SK)]
    case SK = 'SK';

    #[SubdivisionName('Yukon')]
    #[SubdivisionCode(SubdivisionCode::CA_YT)]
    case YT = 'YT';

    public static function instance(mixed $province): self
    {
        return self::parse($province) ?? throw new \UnexpectedValueException(
            \sprintf('Invalid CA Province: %s', \is_string($province) ? $province : \get_debug_type($province)),
        );
    }

    public static function parse(mixed $province): self|null
    {
        if ($province === null || $province instanceof self) {
            return $province;
        }

        if (! \is_string($province) && ! $province instanceof \Stringable) {
            return null;
        }

        static $map = (static function () {
            $map = [];
            foreach (self::cases() as $province) {
                $map[$province->value] = $province;
                $map[\strtoupper($province->label()->value)] = $province;
                $map[$province->code()->value] = $province;
            }
            $map['PEI'] = self::PE;
            $map['NEWFOUNDLAND'] = self::NL;
            $map['LABRADOR'] = self::NL;
            $map['NEWFOUNDLAND/LABRADOR'] = self::NL;
            return $map;
        })();

        return $map[\strtoupper(\str_replace(["'", '.', ','], '', \trim((string)$province)))] ?? null;
    }

    public function label(): SubdivisionName
    {
        static $cache = [];
        return $cache[$this->name] ??= case_attr_fetch($this, SubdivisionName::class);
    }

    public function code(): SubdivisionCode
    {
        static $cache = [];
        return $cache[$this->name] ??= case_attr_fetch($this, SubdivisionCode::class);
    }

    public function getRegion(): Region
    {
        return Region::CA;
    }
}
