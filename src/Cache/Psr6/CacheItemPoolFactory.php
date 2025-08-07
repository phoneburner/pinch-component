<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cache\Psr6;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Component\Cache\CacheDriver;
use Psr\Cache\CacheItemPoolInterface;

#[Contract]
interface CacheItemPoolFactory
{
    public function make(CacheDriver $driver, string|null $namespace = null,): CacheItemPoolInterface;
}
