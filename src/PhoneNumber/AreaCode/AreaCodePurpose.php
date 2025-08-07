<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\PhoneNumber\AreaCode;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Component\PhoneNumber\AreaCode\AreaCodeAware;

#[Contract]
enum AreaCodePurpose: int
{
    case GeneralPurpose = 0;
    case PersonalCommunication = 1;
    case CanadianNonGeographicTariffed = 2;
    case CanadianNonGeographic = 3;
    case InterexchangeCarrier = 4;
    case Government = 5;
    case TollFree = 6;
    case PremiumService = 7;

    public static function lookup(AreaCodeAware $area_code): self
    {
        return $area_code->getAreaCode()->purpose;
    }
}
