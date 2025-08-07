<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cache\Psr6;

use Psr\Cache\CacheItemPoolInterface;

interface FileCacheItemPoolFactory
{
    public function createFileCacheItemPool(
        string $namespace = '',
        string|null $directory = null,
    ): CacheItemPoolInterface;
}
