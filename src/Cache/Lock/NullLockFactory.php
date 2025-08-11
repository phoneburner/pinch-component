<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cache\Lock;

use PhoneBurner\Pinch\Time\Interval\TimeInterval;

final class NullLockFactory implements LockFactory
{
    #[\Override]
    public function make(
        \Stringable|string $key,
        TimeInterval $ttl = new TimeInterval(seconds: 300),
        bool $auto_release = true,
    ): NullLock {
        return new NullLock($ttl);
    }
}
