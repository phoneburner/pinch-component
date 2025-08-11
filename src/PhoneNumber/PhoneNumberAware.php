<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\PhoneNumber;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Component\PhoneNumber\NullablePhoneNumberAware;
use PhoneBurner\Pinch\Component\PhoneNumber\PhoneNumber;

#[Contract]
interface PhoneNumberAware extends NullablePhoneNumberAware
{
    public function getPhoneNumber(): PhoneNumber;
}
