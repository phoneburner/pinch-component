<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\I18n\Subdivision;

use PhoneBurner\Pinch\Component\I18n\Region\Region;
use PhoneBurner\Pinch\Component\I18n\Region\RegionAware;

use function PhoneBurner\Pinch\Enum\case_attr_fetch;

/**
 * United States: 50 States, 1 Federal District, and 6 Territories
 *
 * Note: The six territories have both ISO 3661-1 Alpha 2 and CLDR entries
 * as "country level" regions and ISO 3661-2 entries as political subdivisions
 * of the United States. E.g. Guam is both "GU" and "US-GU".
 */
enum UnitedStatesState: string implements RegionAware
{
    #[SubdivisionName('Alabama')]
    #[SubdivisionCode(SubdivisionCode::US_AL)]
    case AL = 'AL';

    #[SubdivisionName('Alaska')]
    #[SubdivisionCode(SubdivisionCode::US_AK)]
    case AK = 'AK';

    #[SubdivisionName('Arizona')]
    #[SubdivisionCode(SubdivisionCode::US_AZ)]
    case AZ = 'AZ';

    #[SubdivisionName('Arkansas')]
    #[SubdivisionCode(SubdivisionCode::US_AR)]
    case AR = 'AR';

    #[SubdivisionName('California')]
    #[SubdivisionCode(SubdivisionCode::US_CA)]
    case CA = 'CA';

    #[SubdivisionName('Colorado')]
    #[SubdivisionCode(SubdivisionCode::US_CO)]
    case CO = 'CO';

    #[SubdivisionName('Connecticut')]
    #[SubdivisionCode(SubdivisionCode::US_CT)]
    case CT = 'CT';

    #[SubdivisionName('Delaware')]
    #[SubdivisionCode(SubdivisionCode::US_DE)]
    case DE = 'DE';

    #[SubdivisionName('Florida')]
    #[SubdivisionCode(SubdivisionCode::US_FL)]
    case FL = 'FL';

    #[SubdivisionName('Georgia')]
    #[SubdivisionCode(SubdivisionCode::US_GA)]
    case GA = 'GA';

    #[SubdivisionName('Hawaii')]
    #[SubdivisionCode(SubdivisionCode::US_HI)]
    case HI = 'HI';

    #[SubdivisionName('Idaho')]
    #[SubdivisionCode(SubdivisionCode::US_ID)]
    case ID = 'ID';

    #[SubdivisionName('Illinois')]
    #[SubdivisionCode(SubdivisionCode::US_IL)]
    case IL = 'IL';

    #[SubdivisionName('Indiana')]
    #[SubdivisionCode(SubdivisionCode::US_IN)]
    case IN = 'IN';

    #[SubdivisionName('Iowa')]
    #[SubdivisionCode(SubdivisionCode::US_IA)]
    case IA = 'IA';

    #[SubdivisionName('Kansas')]
    #[SubdivisionCode(SubdivisionCode::US_KS)]
    case KS = 'KS';

    #[SubdivisionName('Kentucky')]
    #[SubdivisionCode(SubdivisionCode::US_KY)]
    case KY = 'KY';

    #[SubdivisionName('Louisiana')]
    #[SubdivisionCode(SubdivisionCode::US_LA)]
    case LA = 'LA';

    #[SubdivisionName('Maine')]
    #[SubdivisionCode(SubdivisionCode::US_ME)]
    case ME = 'ME';

    #[SubdivisionName('Maryland')]
    #[SubdivisionCode(SubdivisionCode::US_MD)]
    case MD = 'MD';

    #[SubdivisionName('Massachusetts')]
    #[SubdivisionCode(SubdivisionCode::US_MA)]
    case MA = 'MA';

    #[SubdivisionName('Michigan')]
    #[SubdivisionCode(SubdivisionCode::US_MI)]
    case MI = 'MI';

    #[SubdivisionName('Minnesota')]
    #[SubdivisionCode(SubdivisionCode::US_MN)]
    case MN = 'MN';

    #[SubdivisionName('Mississippi')]
    #[SubdivisionCode(SubdivisionCode::US_MS)]
    case MS = 'MS';

    #[SubdivisionName('Missouri')]
    #[SubdivisionCode(SubdivisionCode::US_MO)]
    case MO = 'MO';

    #[SubdivisionName('Montana')]
    #[SubdivisionCode(SubdivisionCode::US_MT)]
    case MT = 'MT';

    #[SubdivisionName('Nebraska')]
    #[SubdivisionCode(SubdivisionCode::US_NE)]
    case NE = 'NE';

    #[SubdivisionName('Nevada')]
    #[SubdivisionCode(SubdivisionCode::US_NV)]
    case NV = 'NV';

    #[SubdivisionName('New Hampshire')]
    #[SubdivisionCode(SubdivisionCode::US_NH)]
    case NH = 'NH';

    #[SubdivisionName('New Jersey')]
    #[SubdivisionCode(SubdivisionCode::US_NJ)]
    case NJ = 'NJ';

    #[SubdivisionName('New Mexico')]
    #[SubdivisionCode(SubdivisionCode::US_NM)]
    case NM = 'NM';

    #[SubdivisionName('New York')]
    #[SubdivisionCode(SubdivisionCode::US_NY)]
    case NY = 'NY';

    #[SubdivisionName('North Carolina')]
    #[SubdivisionCode(SubdivisionCode::US_NC)]
    case NC = 'NC';

    #[SubdivisionName('North Dakota')]
    #[SubdivisionCode(SubdivisionCode::US_ND)]
    case ND = 'ND';

    #[SubdivisionName('Ohio')]
    #[SubdivisionCode(SubdivisionCode::US_OH)]
    case OH = 'OH';

    #[SubdivisionName('Oklahoma')]
    #[SubdivisionCode(SubdivisionCode::US_OK)]
    case OK = 'OK';

    #[SubdivisionName('Oregon')]
    #[SubdivisionCode(SubdivisionCode::US_OR)]
    case OR = 'OR';

    #[SubdivisionName('Pennsylvania')]
    #[SubdivisionCode(SubdivisionCode::US_PA)]
    case PA = 'PA';

    #[SubdivisionName('Rhode Island')]
    #[SubdivisionCode(SubdivisionCode::US_RI)]
    case RI = 'RI';

    #[SubdivisionName('South Carolina')]
    #[SubdivisionCode(SubdivisionCode::US_SC)]
    case SC = 'SC';

    #[SubdivisionName('South Dakota')]
    #[SubdivisionCode(SubdivisionCode::US_SD)]
    case SD = 'SD';

    #[SubdivisionName('Tennessee')]
    #[SubdivisionCode(SubdivisionCode::US_TN)]
    case TN = 'TN';

    #[SubdivisionName('Texas')]
    #[SubdivisionCode(SubdivisionCode::US_TX)]
    case TX = 'TX';

    #[SubdivisionName('Utah')]
    #[SubdivisionCode(SubdivisionCode::US_UT)]
    case UT = 'UT';

    #[SubdivisionName('Vermont')]
    #[SubdivisionCode(SubdivisionCode::US_VT)]
    case VT = 'VT';

    #[SubdivisionName('Virginia')]
    #[SubdivisionCode(SubdivisionCode::US_VA)]
    case VA = 'VA';

    #[SubdivisionName('Washington')]
    #[SubdivisionCode(SubdivisionCode::US_WA)]
    case WA = 'WA';

    #[SubdivisionName('West Virginia')]
    #[SubdivisionCode(SubdivisionCode::US_WV)]
    case WV = 'WV';

    #[SubdivisionName('Wisconsin')]
    #[SubdivisionCode(SubdivisionCode::US_WI)]
    case WI = 'WI';

    #[SubdivisionName('Wyoming')]
    #[SubdivisionCode(SubdivisionCode::US_WY)]
    case WY = 'WY';

    #[SubdivisionName('District of Columbia')]
    #[SubdivisionCode(SubdivisionCode::US_DC)]
    case DC = 'DC';

    #[SubdivisionName('American Samoa')]
    #[SubdivisionCode(SubdivisionCode::US_AS)]
    case AS = 'AS';

    #[SubdivisionName('Guam')]
    #[SubdivisionCode(SubdivisionCode::US_GU)]
    case GU = 'GU';

    #[SubdivisionName('Northern Mariana Islands')]
    #[SubdivisionCode(SubdivisionCode::US_MP)]
    case MP = 'MP';

    #[SubdivisionName('Puerto Rico')]
    #[SubdivisionCode(SubdivisionCode::US_PR)]
    case PR = 'PR';

    #[SubdivisionName('US Virgin Islands')]
    #[SubdivisionCode(SubdivisionCode::US_VI)]
    case VI = 'VI';

    public static function instance(mixed $state): self
    {
        return self::parse($state) ?? throw new \UnexpectedValueException(
            \sprintf('Invalid US State: %s', \is_string($state) ? $state : \get_debug_type($state)),
        );
    }

    public static function parse(mixed $state): self|null
    {
        if ($state === null || $state instanceof self) {
            return $state;
        }

        if (! \is_string($state) && ! $state instanceof \Stringable) {
            return null;
        }

        static $map = (static function () {
            $map = [];
            foreach (self::cases() as $state) {
                $map[$state->value] = $state;
                $map[\strtoupper($state->label()->value)] = $state;
                $map[$state->code()->value] = $state;
            }
            $map[\strtoupper('WASHINGTON DC')] = self::DC;
            return $map;
        })();

        return $map[\strtoupper(\str_replace(["'", '.', ','], '', \trim((string)$state)))] ?? null;
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
        return Region::US;
    }
}
