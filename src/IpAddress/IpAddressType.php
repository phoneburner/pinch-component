<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\IpAddress;

use PhoneBurner\Pinch\Attribute\Usage\Contract;

#[Contract]
enum IpAddressType
{
    case IPv4;
    case IPv6;
}
