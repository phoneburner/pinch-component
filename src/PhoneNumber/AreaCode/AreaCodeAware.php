<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\PhoneNumber\AreaCode;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Component\PhoneNumber\AreaCode\AreaCode;
use PhoneBurner\Pinch\Component\PhoneNumber\AreaCode\NullableAreaCodeAware;

#[Contract]
interface AreaCodeAware extends NullableAreaCodeAware
{
    public function getAreaCode(): AreaCode;
}
