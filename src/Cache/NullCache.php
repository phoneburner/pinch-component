<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cache;

use PhoneBurner\Pinch\Component\Cache\Psr6\NullCachePool;

final class NullCache extends CacheAdapter
{
    public function __construct()
    {
        parent::__construct(new NullCachePool());
    }
}
