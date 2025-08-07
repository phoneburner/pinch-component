<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cache\Warmup;

use PhoneBurner\Pinch\Component\Cache\CacheKey;

readonly class StaticCacheDataStruct
{
    public function __construct(
        public CacheKey $key,
        public mixed $value,
    ) {
    }
}
