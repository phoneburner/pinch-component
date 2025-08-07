<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\I18n\Region;

interface RegionAware
{
    public function getRegion(): Region;
}
