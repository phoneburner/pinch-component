<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\PhoneNumber;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Component\PhoneNumber\NullablePhoneNumber;

#[Contract]
interface NullablePhoneNumberAware
{
    public function getValue(): NullablePhoneNumber;
}
