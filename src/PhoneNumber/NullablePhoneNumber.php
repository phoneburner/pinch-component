<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\PhoneNumber;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Component\PhoneNumber\E164;

#[Contract]
interface NullablePhoneNumber
{
    public function toE164(): E164|null;
}
