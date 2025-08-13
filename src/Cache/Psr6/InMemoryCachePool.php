<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cache\Psr6;

use PhoneBurner\Pinch\Component\Cache\CacheKey;
use PhoneBurner\Pinch\Time\Clock\Clock;
use PhoneBurner\Pinch\Time\Clock\SystemClock;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class InMemoryCachePool implements CacheItemPoolInterface
{
    /**
     * @var array<string, CacheItemInterface>
     */
    private array $items = [];

    /**
     * @var array<string, CacheItemInterface>
     */
    private array $deferred = [];

    public function __construct(private readonly Clock $clock = new SystemClock())
    {
    }

    public function getItem(CacheKey|string|\Stringable $key): CacheItemInterface
    {
        $cache_key = self::key($key);
        return $this->items[$cache_key->normalized] ?? new CacheItem($cache_key, $this->clock);
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

    public function hasItem(CacheKey|string $key): bool
    {
        return $this->getItem($key)->isHit();
    }

    public function clear(): bool
    {
        $this->items = [];
        $this->deferred = [];
        return true;
    }

    public function deleteItem(CacheKey|string $key): bool
    {
        unset($this->items[self::key($key)->normalized]);
        return true;
    }

    /**
     * @param array<string|CacheKey> $keys
     */
    public function deleteItems(array $keys): bool
    {
        foreach ($keys as $key) {
            $this->deleteItem($key);
        }

        return true;
    }

    public function save(CacheItemInterface $item): bool
    {
        $this->items[$item->getKey()] = $item;
        return true;
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->deferred[$item->getKey()] = $item;
        return true;
    }

    public function commit(): bool
    {
        foreach ($this->deferred as $item) {
            $this->save($item);
        }

        $this->deferred = [];
        return true;
    }

    private static function key(CacheKey|string|\Stringable $key): CacheKey
    {
        if ($key instanceof CacheKey) {
            return $key;
        }

        // Convert Stringable to string before creating CacheKey
        return new CacheKey((string)$key);
    }
}
