<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Fixtures;

use PhoneBurner\Pinch\Component\IpAddress\IpAddressType;

final readonly class IpAddressTestStruct
{
    public function __construct(
        public string $value,
        public IpAddressType $type,
    ) {
    }
}
