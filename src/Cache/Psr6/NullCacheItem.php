<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cache\Psr6;

use PhoneBurner\Pinch\Component\Cache\CacheKey;
use Psr\Cache\CacheItemInterface;

class NullCacheItem implements CacheItemInterface
{
    public function __construct(
        public readonly CacheKey $key,
    ) {
    }

    public function getKey(): string
    {
        return $this->key->normalized;
    }

    public function get(): null
    {
        return null;
    }

    public function isHit(): bool
    {
        return false;
    }

    public function set(mixed $value): static
    {
        return $this;
    }

    public function expiresAt(\DateTimeInterface|null $expiration): static
    {
        return $this;
    }

    public function expiresAfter(\DateInterval|int|null $time): static
    {
        return $this;
    }
}
