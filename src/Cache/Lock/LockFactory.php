<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cache\Lock;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Time\TimeInterval\TimeInterval;

#[Contract]
interface LockFactory
{
    public function make(
        \Stringable|string $key,
        TimeInterval $ttl = new TimeInterval(seconds: 300),
        bool $auto_release = true,
    ): Lock;
}
