<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\I18n\Region;

use PhoneBurner\Pinch\Component\I18n\IsoLocale;

use function PhoneBurner\Pinch\Enum\case_attr_find;

enum Region: string implements RegionAware
{
    #[RegionName(RegionName::AC)]
    case AC = 'AC'; // Ascension Island

    #[RegionName(RegionName::AD)]
    case AD = 'AD'; // Andorra

    #[RegionName(RegionName::AE)]
    case AE = 'AE'; // United Arab Emirates

    #[RegionName(RegionName::AF)]
    case AF = 'AF'; // Afghanistan

    #[RegionName(RegionName::AG)]
    case AG = 'AG'; // Antigua & Barbuda

    #[RegionName(RegionName::AI)]
    case AI = 'AI'; // Anguilla

    #[RegionName(RegionName::AL)]
    case AL = 'AL'; // Albania

    #[RegionName(RegionName::AM)]
    case AM = 'AM'; // Armenia

    #[RegionName(RegionName::AO)]
    case AO = 'AO'; // Angola

    #[RegionName(RegionName::AQ)]
    case AQ = 'AQ'; // Antarctica

    #[RegionName(RegionName::AR)]
    case AR = 'AR'; // Argentina

    #[RegionName(RegionName::AS)]
    case AS = 'AS'; // American Samoa

    #[RegionName(RegionName::AT)]
    case AT = 'AT'; // Austria

    #[RegionName(RegionName::AU)]
    case AU = 'AU'; // Australia

    #[RegionName(RegionName::AW)]
    case AW = 'AW'; // Aruba

    #[RegionName(RegionName::AX)]
    case AX = 'AX'; // Åland Islands

    #[RegionName(RegionName::AZ)]
    case AZ = 'AZ'; // Azerbaijan

    #[RegionName(RegionName::BA)]
    case BA = 'BA'; // Bosnia & Herzegovina

    #[RegionName(RegionName::BB)]
    case BB = 'BB'; // Barbados

    #[RegionName(RegionName::BD)]
    case BD = 'BD'; // Bangladesh

    #[RegionName(RegionName::BE)]
    case BE = 'BE'; // Belgium

    #[RegionName(RegionName::BF)]
    case BF = 'BF'; // Burkina Faso

    #[RegionName(RegionName::BG)]
    case BG = 'BG'; // Bulgaria

    #[RegionName(RegionName::BH)]
    case BH = 'BH'; // Bahrain

    #[RegionName(RegionName::BI)]
    case BI = 'BI'; // Burundi

    #[RegionName(RegionName::BJ)]
    case BJ = 'BJ'; // Benin

    #[RegionName(RegionName::BL)]
    case BL = 'BL'; // St. Barthélemy

    #[RegionName(RegionName::BM)]
    case BM = 'BM'; // Bermuda

    #[RegionName(RegionName::BN)]
    case BN = 'BN'; // Brunei

    #[RegionName(RegionName::BO)]
    case BO = 'BO'; // Bolivia

    #[RegionName(RegionName::BQ)]
    case BQ = 'BQ'; // Caribbean Netherlands

    #[RegionName(RegionName::BR)]
    case BR = 'BR'; // Brazil

    #[RegionName(RegionName::BS)]
    case BS = 'BS'; // Bahamas

    #[RegionName(RegionName::BT)]
    case BT = 'BT'; // Bhutan

    #[RegionName(RegionName::BV)]
    case BV = 'BV'; // Bouvet Island

    #[RegionName(RegionName::BW)]
    case BW = 'BW'; // Botswana

    #[RegionName(RegionName::BY)]
    case BY = 'BY'; // Belarus

    #[RegionName(RegionName::BZ)]
    case BZ = 'BZ'; // Belize

    #[RegionName(RegionName::CA)]
    case CA = 'CA'; // Canada

    #[RegionName(RegionName::CC)]
    case CC = 'CC'; // Cocos (Keeling) Islands

    #[RegionName(RegionName::CD)]
    case CD = 'CD'; // Congo - Kinshasa

    #[RegionName(RegionName::CF)]
    case CF = 'CF'; // Central African Republic

    #[RegionName(RegionName::CG)]
    case CG = 'CG'; // Congo - Brazzaville

    #[RegionName(RegionName::CH)]
    case CH = 'CH'; // Switzerland

    #[RegionName(RegionName::CI)]
    case CI = 'CI'; // Côte d’Ivoire

    #[RegionName(RegionName::CK)]
    case CK = 'CK'; // Cook Islands

    #[RegionName(RegionName::CL)]
    case CL = 'CL'; // Chile

    #[RegionName(RegionName::CM)]
    case CM = 'CM'; // Cameroon

    #[RegionName(RegionName::CN)]
    case CN = 'CN'; // China

    #[RegionName(RegionName::CO)]
    case CO = 'CO'; // Colombia

    #[RegionName(RegionName::CR)]
    case CR = 'CR'; // Costa Rica

    #[RegionName(RegionName::CU)]
    case CU = 'CU'; // Cuba

    #[RegionName(RegionName::CV)]
    case CV = 'CV'; // Cape Verde

    #[RegionName(RegionName::CW)]
    case CW = 'CW'; // Curaçao

    #[RegionName(RegionName::CX)]
    case CX = 'CX'; // Christmas Island

    #[RegionName(RegionName::CY)]
    case CY = 'CY'; // Cyprus

    #[RegionName(RegionName::CZ)]
    case CZ = 'CZ'; // Czechia

    #[RegionName(RegionName::DE)]
    case DE = 'DE'; // Germany

    #[RegionName(RegionName::DG)]
    case DG = 'DG'; // Diego Garcia

    #[RegionName(RegionName::DJ)]
    case DJ = 'DJ'; // Djibouti

    #[RegionName(RegionName::DK)]
    case DK = 'DK'; // Denmark

    #[RegionName(RegionName::DM)]
    case DM = 'DM'; // Dominica

    #[RegionName(RegionName::DO)]
    case DO = 'DO'; // Dominican Republic

    #[RegionName(RegionName::DZ)]
    case DZ = 'DZ'; // Algeria

    #[RegionName(RegionName::EA)]
    case EA = 'EA'; // Ceuta & Melilla

    #[RegionName(RegionName::EC)]
    case EC = 'EC'; // Ecuador

    #[RegionName(RegionName::EE)]
    case EE = 'EE'; // Estonia

    #[RegionName(RegionName::EG)]
    case EG = 'EG'; // Egypt

    #[RegionName(RegionName::EH)]
    case EH = 'EH'; // Western Sahara

    #[RegionName(RegionName::ER)]
    case ER = 'ER'; // Eritrea

    #[RegionName(RegionName::ES)]
    case ES = 'ES'; // Spain

    #[RegionName(RegionName::ET)]
    case ET = 'ET'; // Ethiopia

    #[RegionName(RegionName::FI)]
    case FI = 'FI'; // Finland

    #[RegionName(RegionName::FJ)]
    case FJ = 'FJ'; // Fiji

    #[RegionName(RegionName::FK)]
    case FK = 'FK'; // Falkland Islands

    #[RegionName(RegionName::FM)]
    case FM = 'FM'; // Micronesia

    #[RegionName(RegionName::FO)]
    case FO = 'FO'; // Faroe Islands

    #[RegionName(RegionName::FR)]
    case FR = 'FR'; // France

    #[RegionName(RegionName::GA)]
    case GA = 'GA'; // Gabon

    #[RegionName(RegionName::GB)]
    case GB = 'GB'; // United Kingdom

    #[RegionName(RegionName::GD)]
    case GD = 'GD'; // Grenada

    #[RegionName(RegionName::GE)]
    case GE = 'GE'; // Georgia

    #[RegionName(RegionName::GF)]
    case GF = 'GF'; // French Guiana

    #[RegionName(RegionName::GG)]
    case GG = 'GG'; // Guernsey

    #[RegionName(RegionName::GH)]
    case GH = 'GH'; // Ghana

    #[RegionName(RegionName::GI)]
    case GI = 'GI'; // Gibraltar

    #[RegionName(RegionName::GL)]
    case GL = 'GL'; // Greenland

    #[RegionName(RegionName::GM)]
    case GM = 'GM'; // Gambia

    #[RegionName(RegionName::GN)]
    case GN = 'GN'; // Guinea

    #[RegionName(RegionName::GP)]
    case GP = 'GP'; // Guadeloupe

    #[RegionName(RegionName::GQ)]
    case GQ = 'GQ'; // Equatorial Guinea

    #[RegionName(RegionName::GR)]
    case GR = 'GR'; // Greece

    #[RegionName(RegionName::GS)]
    case GS = 'GS'; // South Georgia & South Sandwich Islands

    #[RegionName(RegionName::GT)]
    case GT = 'GT'; // Guatemala

    #[RegionName(RegionName::GU)]
    case GU = 'GU'; // Guam

    #[RegionName(RegionName::GW)]
    case GW = 'GW'; // Guinea-Bissau

    #[RegionName(RegionName::GY)]
    case GY = 'GY'; // Guyana

    #[RegionName(RegionName::HK)]
    case HK = 'HK'; // Hong Kong SAR China

    #[RegionName(RegionName::HN)]
    case HN = 'HN'; // Honduras

    #[RegionName(RegionName::HR)]
    case HR = 'HR'; // Croatia

    #[RegionName(RegionName::HT)]
    case HT = 'HT'; // Haiti

    #[RegionName(RegionName::HU)]
    case HU = 'HU'; // Hungary

    #[RegionName(RegionName::IC)]
    case IC = 'IC'; // Canary Islands

    #[RegionName(RegionName::ID)]
    case ID = 'ID'; // Indonesia

    #[RegionName(RegionName::IE)]
    case IE = 'IE'; // Ireland

    #[RegionName(RegionName::IL)]
    case IL = 'IL'; // Israel

    #[RegionName(RegionName::IM)]
    case IM = 'IM'; // Isle of Man

    #[RegionName(RegionName::IN)]
    case IN = 'IN'; // India

    #[RegionName(RegionName::IO)]
    case IO = 'IO'; // British Indian Ocean Territory

    #[RegionName(RegionName::IQ)]
    case IQ = 'IQ'; // Iraq

    #[RegionName(RegionName::IR)]
    case IR = 'IR'; // Iran

    #[RegionName(RegionName::IS)]
    case IS = 'IS'; // Iceland

    #[RegionName(RegionName::IT)]
    case IT = 'IT'; // Italy

    #[RegionName(RegionName::JE)]
    case JE = 'JE'; // Jersey

    #[RegionName(RegionName::JM)]
    case JM = 'JM'; // Jamaica

    #[RegionName(RegionName::JO)]
    case JO = 'JO'; // Jordan

    #[RegionName(RegionName::JP)]
    case JP = 'JP'; // Japan

    #[RegionName(RegionName::KE)]
    case KE = 'KE'; // Kenya

    #[RegionName(RegionName::KG)]
    case KG = 'KG'; // Kyrgyzstan

    #[RegionName(RegionName::KH)]
    case KH = 'KH'; // Cambodia

    #[RegionName(RegionName::KI)]
    case KI = 'KI'; // Kiribati

    #[RegionName(RegionName::KM)]
    case KM = 'KM'; // Comoros

    #[RegionName(RegionName::KN)]
    case KN = 'KN'; // St. Kitts & Nevis

    #[RegionName(RegionName::KP)]
    case KP = 'KP'; // North Korea

    #[RegionName(RegionName::KR)]
    case KR = 'KR'; // South Korea

    #[RegionName(RegionName::KW)]
    case KW = 'KW'; // Kuwait

    #[RegionName(RegionName::KY)]
    case KY = 'KY'; // Cayman Islands

    #[RegionName(RegionName::KZ)]
    case KZ = 'KZ'; // Kazakhstan

    #[RegionName(RegionName::LA)]
    case LA = 'LA'; // Laos

    #[RegionName(RegionName::LB)]
    case LB = 'LB'; // Lebanon

    #[RegionName(RegionName::LC)]
    case LC = 'LC'; // St. Lucia

    #[RegionName(RegionName::LI)]
    case LI = 'LI'; // Liechtenstein

    #[RegionName(RegionName::LK)]
    case LK = 'LK'; // Sri Lanka

    #[RegionName(RegionName::LR)]
    case LR = 'LR'; // Liberia

    #[RegionName(RegionName::LS)]
    case LS = 'LS'; // Lesotho

    #[RegionName(RegionName::LT)]
    case LT = 'LT'; // Lithuania

    #[RegionName(RegionName::LU)]
    case LU = 'LU'; // Luxembourg

    #[RegionName(RegionName::LV)]
    case LV = 'LV'; // Latvia

    #[RegionName(RegionName::LY)]
    case LY = 'LY'; // Libya

    #[RegionName(RegionName::MA)]
    case MA = 'MA'; // Morocco

    #[RegionName(RegionName::MC)]
    case MC = 'MC'; // Monaco

    #[RegionName(RegionName::MD)]
    case MD = 'MD'; // Moldova

    #[RegionName(RegionName::ME)]
    case ME = 'ME'; // Montenegro

    #[RegionName(RegionName::MF)]
    case MF = 'MF'; // St. Martin

    #[RegionName(RegionName::MG)]
    case MG = 'MG'; // Madagascar

    #[RegionName(RegionName::MH)]
    case MH = 'MH'; // Marshall Islands

    #[RegionName(RegionName::MK)]
    #[RegionName('Северна Македонија', IsoLocale::MK_MK)]
    case MK = 'MK'; // North Macedonia

    #[RegionName(RegionName::ML)]
    case ML = 'ML'; // Mali

    #[RegionName(RegionName::MM)]
    case MM = 'MM'; // Myanmar (Burma)

    #[RegionName(RegionName::MN)]
    case MN = 'MN'; // Mongolia

    #[RegionName(RegionName::MO)]
    case MO = 'MO'; // Macao SAR China

    #[RegionName(RegionName::MP)]
    case MP = 'MP'; // Northern Mariana Islands

    #[RegionName(RegionName::MQ)]
    case MQ = 'MQ'; // Martinique

    #[RegionName(RegionName::MR)]
    case MR = 'MR'; // Mauritania

    #[RegionName(RegionName::MS)]
    case MS = 'MS'; // Montserrat

    #[RegionName(RegionName::MT)]
    case MT = 'MT'; // Malta

    #[RegionName(RegionName::MU)]
    case MU = 'MU'; // Mauritius

    #[RegionName(RegionName::MV)]
    case MV = 'MV'; // Maldives

    #[RegionName(RegionName::MW)]
    case MW = 'MW'; // Malawi

    #[RegionName(RegionName::MX)]
    case MX = 'MX'; // Mexico

    #[RegionName(RegionName::MY)]
    case MY = 'MY'; // Malaysia

    #[RegionName(RegionName::MZ)]
    case MZ = 'MZ'; // Mozambique

    #[RegionName(RegionName::NA)]
    case NA = 'NA'; // Namibia

    #[RegionName(RegionName::NC)]
    case NC = 'NC'; // New Caledonia

    #[RegionName(RegionName::NE)]
    case NE = 'NE'; // Niger

    #[RegionName(RegionName::NF)]
    case NF = 'NF'; // Norfolk Island

    #[RegionName(RegionName::NG)]
    case NG = 'NG'; // Nigeria

    #[RegionName(RegionName::NI)]
    case NI = 'NI'; // Nicaragua

    #[RegionName(RegionName::NL)]
    case NL = 'NL'; // Netherlands

    #[RegionName(RegionName::NO)]
    case NO = 'NO'; // Norway

    #[RegionName(RegionName::NP)]
    case NP = 'NP'; // Nepal

    #[RegionName(RegionName::NR)]
    case NR = 'NR'; // Nauru

    #[RegionName(RegionName::NU)]
    case NU = 'NU'; // Niue

    #[RegionName(RegionName::NZ)]
    case NZ = 'NZ'; // New Zealand

    #[RegionName(RegionName::OM)]
    case OM = 'OM'; // Oman

    #[RegionName(RegionName::PA)]
    case PA = 'PA'; // Panama

    #[RegionName(RegionName::PE)]
    case PE = 'PE'; // Peru

    #[RegionName(RegionName::PF)]
    case PF = 'PF'; // French Polynesia

    #[RegionName(RegionName::PG)]
    case PG = 'PG'; // Papua New Guinea

    #[RegionName(RegionName::PH)]
    case PH = 'PH'; // Philippines

    #[RegionName(RegionName::PK)]
    case PK = 'PK'; // Pakistan

    #[RegionName(RegionName::PL)]
    case PL = 'PL'; // Poland

    #[RegionName(RegionName::PM)]
    case PM = 'PM'; // St. Pierre & Miquelon

    #[RegionName(RegionName::PN)]
    case PN = 'PN'; // Pitcairn Islands

    #[RegionName(RegionName::PR)]
    case PR = 'PR'; // Puerto Rico

    #[RegionName(RegionName::PS)]
    case PS = 'PS'; // Palestinian Territories

    #[RegionName(RegionName::PT)]
    case PT = 'PT'; // Portugal

    #[RegionName(RegionName::PW)]
    case PW = 'PW'; // Palau

    #[RegionName(RegionName::PY)]
    case PY = 'PY'; // Paraguay

    #[RegionName(RegionName::QA)]
    case QA = 'QA'; // Qatar

    #[RegionName(RegionName::RE)]
    case RE = 'RE'; // Réunion

    #[RegionName(RegionName::RO)]
    case RO = 'RO'; // Romania

    #[RegionName(RegionName::RS)]
    case RS = 'RS'; // Serbia

    #[RegionName(RegionName::RU)]
    case RU = 'RU'; // Russia

    #[RegionName(RegionName::RW)]
    case RW = 'RW'; // Rwanda

    #[RegionName(RegionName::SA)]
    case SA = 'SA'; // Saudi Arabia

    #[RegionName(RegionName::SB)]
    case SB = 'SB'; // Solomon Islands

    #[RegionName(RegionName::SC)]
    case SC = 'SC'; // Seychelles

    #[RegionName(RegionName::SD)]
    case SD = 'SD'; // Sudan

    #[RegionName(RegionName::SE)]
    case SE = 'SE'; // Sweden

    #[RegionName(RegionName::SG)]
    case SG = 'SG'; // Singapore

    #[RegionName(RegionName::SH)]
    case SH = 'SH'; // St. Helena

    #[RegionName(RegionName::SI)]
    case SI = 'SI'; // Slovenia

    #[RegionName(RegionName::SJ)]
    case SJ = 'SJ'; // Svalbard & Jan Mayen

    #[RegionName(RegionName::SK)]
    case SK = 'SK'; // Slovakia

    #[RegionName(RegionName::SL)]
    case SL = 'SL'; // Sierra Leone

    #[RegionName(RegionName::SM)]
    case SM = 'SM'; // San Marino

    #[RegionName(RegionName::SN)]
    case SN = 'SN'; // Senegal

    #[RegionName(RegionName::SO)]
    case SO = 'SO'; // Somalia

    #[RegionName(RegionName::SR)]
    case SR = 'SR'; // Suriname

    #[RegionName(RegionName::SS)]
    case SS = 'SS'; // South Sudan

    #[RegionName(RegionName::ST)]
    case ST = 'ST'; // São Tomé & Príncipe

    #[RegionName(RegionName::SV)]
    case SV = 'SV'; // El Salvador

    #[RegionName(RegionName::SX)]
    case SX = 'SX'; // Sint Maarten

    #[RegionName(RegionName::SY)]
    case SY = 'SY'; // Syria

    #[RegionName(RegionName::SZ)]
    case SZ = 'SZ'; // Eswatini

    #[RegionName(RegionName::TA)]
    case TA = 'TA'; // Tristan da Cunha

    #[RegionName(RegionName::TC)]
    case TC = 'TC'; // Turks & Caicos Islands

    #[RegionName(RegionName::TD)]
    case TD = 'TD'; // Chad

    #[RegionName(RegionName::TF)]
    case TF = 'TF'; // French Southern Territories

    #[RegionName(RegionName::TG)]
    case TG = 'TG'; // Togo

    #[RegionName(RegionName::TH)]
    case TH = 'TH'; // Thailand

    #[RegionName(RegionName::TJ)]
    case TJ = 'TJ'; // Tajikistan

    #[RegionName(RegionName::TK)]
    case TK = 'TK'; // Tokelau

    #[RegionName(RegionName::TL)]
    case TL = 'TL'; // Timor-Leste

    #[RegionName(RegionName::TM)]
    case TM = 'TM'; // Turkmenistan

    #[RegionName(RegionName::TN)]
    case TN = 'TN'; // Tunisia

    #[RegionName(RegionName::TO)]
    case TO = 'TO'; // Tonga

    #[RegionName(RegionName::TR)]
    case TR = 'TR'; // Turkey

    #[RegionName(RegionName::TT)]
    case TT = 'TT'; // Trinidad & Tobago

    #[RegionName(RegionName::TV)]
    case TV = 'TV'; // Tuvalu

    #[RegionName(RegionName::TW)]
    case TW = 'TW'; // Taiwan

    #[RegionName(RegionName::TZ)]
    case TZ = 'TZ'; // Tanzania

    #[RegionName(RegionName::UA)]
    case UA = 'UA'; // Ukraine

    #[RegionName(RegionName::UG)]
    case UG = 'UG'; // Uganda

    #[RegionName(RegionName::UM)]
    case UM = 'UM'; // United States Minor Outlying Islands

    #[RegionName(RegionName::US)]
    case US = 'US'; // United States

    #[RegionName(RegionName::UY)]
    case UY = 'UY'; // Uruguay

    #[RegionName(RegionName::UZ)]
    case UZ = 'UZ'; // Uzbekistan

    #[RegionName(RegionName::VA)]
    case VA = 'VA'; // Vatican City

    #[RegionName(RegionName::VC)]
    case VC = 'VC'; // St. Vincent & Grenadines

    #[RegionName(RegionName::VE)]
    case VE = 'VE'; // Venezuela

    #[RegionName(RegionName::VG)]
    case VG = 'VG'; // British Virgin Islands

    #[RegionName(RegionName::VI)]
    case VI = 'VI'; // U.S. Virgin Islands

    #[RegionName(RegionName::VN)]
    case VN = 'VN'; // Vietnam

    #[RegionName(RegionName::VU)]
    case VU = 'VU'; // Vanuatu

    #[RegionName(RegionName::WF)]
    case WF = 'WF'; // Wallis & Futuna

    #[RegionName(RegionName::WS)]
    case WS = 'WS'; // Samoa

    #[RegionName(RegionName::YE)]
    case YE = 'YE'; // Yemen

    #[RegionName(RegionName::YT)]
    case YT = 'YT'; // Mayotte

    #[RegionName(RegionName::ZA)]
    case ZA = 'ZA'; // South Africa

    #[RegionName(RegionName::ZM)]
    case ZM = 'ZM'; // Zambia

    #[RegionName(RegionName::ZW)]
    case ZW = 'ZW'; // Zimbabwe

    #[RegionName(RegionName::ZZ)]
    case ZZ = 'ZZ'; // Unknown Region

    /** Case Insensitive Matching */
    public static function instance(mixed $value): self
    {
        return self::cast($value) ?? throw new \UnexpectedValueException();
    }

    public static function cast(mixed $value): self|null
    {
        return match (true) {
            $value instanceof self, $value === null => $value,
            $value instanceof RegionAware => $value->getRegion(),
            $value instanceof \Stringable => self::cast((string)$value),
            ! \is_string($value) => null,
            \strlen($value) === 2 => self::tryFrom(\strtoupper($value)),
            (bool)\preg_match('/^[A-Za-z]{2}\-[0-9A-Za-z]{2,3}$/', $value) => self::tryFrom(\strtoupper(\substr($value, 0, 2))),
            default => null,
        };
    }

    public function name(IsoLocale $locale = IsoLocale::EN_US): RegionName
    {
        $names = case_attr_find($this, RegionName::class);
        return \array_find($names, static fn(RegionName $attr): bool => $attr->locale === $locale)
            ?? $names[0]
            ?? throw new \LogicException('Missing Region Name Attribute');
    }

    public function getRegion(): self
    {
        return $this;
    }
}
