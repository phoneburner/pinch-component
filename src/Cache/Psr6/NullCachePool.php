<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cache\Psr6;

use PhoneBurner\Pinch\Component\Cache\CacheKey;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

final readonly class NullCachePool implements CacheItemPoolInterface
{
    private static function key(CacheKey|string|\Stringable $key): CacheKey
    {
        if ($key instanceof CacheKey) {
            return $key;
        }

        return new CacheKey((string)$key);
    }

    public function getItem(CacheKey|string|\Stringable $key): CacheItemInterface
    {
        return new NullCacheItem(self::key($key));
    }

    /**
     * Note: output array uses the same string key value as key, even if casting
     * it to a CacheKey would change it. This is to maintain the same interface as
     * the PSR-6 interface.
     *
     * @param array<string|CacheKey|\Stringable> $keys
     * @return array<CacheItemInterface>
     */
    public function getItems(array $keys = []): array
    {
        $items = [];
        foreach ($keys as $key) {
            $items[(string)$key] = $this->getItem($key);
        }

        return $items;
    }

    public function hasItem(CacheKey|string|\Stringable $key): bool
    {
        return false;
    }

    public function clear(): bool
    {
        return true;
    }

    public function deleteItem(CacheKey|string|\Stringable $key): bool
    {
        return true;
    }

    /**
     * @param array<string|CacheKey|\Stringable> $keys
     */
    public function deleteItems(array $keys): bool
    {
        return true;
    }

    public function save(CacheItemInterface $item): bool
    {
        return true;
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        return true;
    }

    public function commit(): bool
    {
        return true;
    }
}
