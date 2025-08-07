<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\PhoneNumber;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Component\PhoneNumber\E164;
use PhoneBurner\Pinch\Component\PhoneNumber\NullablePhoneNumber;

#[Contract]
interface PhoneNumber extends NullablePhoneNumber
{
    public function toE164(): E164;
}
