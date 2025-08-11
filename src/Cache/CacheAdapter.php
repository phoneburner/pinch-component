<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cache;

use PhoneBurner\Pinch\Component\Cache\Exception\CacheWriteFailed;
use PhoneBurner\Pinch\Component\Cache\Psr6\InMemoryCachePool;
use PhoneBurner\Pinch\Time\Interval\TimeInterval;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Adapts a PSR-6 cache instance both our Cache interface and the PSR-16 interface
 *
 * @link https://www.php-fig.org/psr/psr-6/
 */
class CacheAdapter implements Cache, CacheInterface, CacheItemPoolInterface
{
    public function __construct(
        private readonly CacheItemPoolInterface $pool = new InMemoryCachePool(),
    ) {
    }

    public function get(\Stringable|string $key, mixed $default = null): mixed
    {
        $item = $this->pool->getItem(self::normalize($key));
        return $item->isHit() ? $item->get() : $default;
    }

    /**
     * @param iterable<string|\Stringable> $keys
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $items = [];
        foreach ($this->getItems($keys) as $item) {
            $items[$item->getKey()] = $item->isHit() ? $item->get() : $default;
        }

        return $items;
    }

    public function set(
        \Stringable|string $key,
        mixed $value,
        \DateInterval|int|null $ttl = new TimeInterval(seconds: 300),
    ): bool {
        $item = $this->getItem($key)->set($value)->expiresAfter(self::ttl($ttl));
        return $this->save($item);
    }

    public function setMultiple(
        iterable $values,
        \DateInterval|int|null $ttl = new TimeInterval(seconds: 300),
    ): bool {
        $ttl = self::ttl($ttl);
        foreach ($values as $key => $value) {
            \assert(\is_string($key) || $key instanceof \Stringable);
            $item = $this->pool->getItem(self::normalize($key))->set($value)->expiresAfter($ttl);
            $this->pool->saveDeferred($item);
        }

        return $this->pool->commit();
    }

    public function delete(\Stringable|string $key): bool
    {
        return $this->pool->deleteItem(self::normalize($key));
    }

    public function deleteMultiple(iterable $keys): bool
    {
        return $this->pool->deleteItems(self::keys($keys));
    }

    public function remember(
        \Stringable|string $key,
        callable $callback,
        \DateInterval|int|null $ttl = new TimeInterval(seconds: 300),
        bool $force_refresh = false,
    ): mixed {
        $key = self::normalize($key);
        $value = $force_refresh ? null : $this->get($key);
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        if ($value !== null) {
            $this->set($key, $value, self::ttl($ttl)) || throw new CacheWriteFailed('set: ' . $key);
        }

        return $value;
    }

    public function forget(\Stringable|string $key): mixed
    {
        $key = self::normalize($key);
        $value = $this->get($key);
        if ($value !== null) {
            $this->delete($key) || throw new CacheWriteFailed('delete: ' . $key);
        }

        return $value;
    }

    public function clear(): bool
    {
        return $this->pool->clear();
    }

    public function has(string $key): bool
    {
        return $this->pool->hasItem($key);
    }

    private static function normalize(\Stringable|string $key): string
    {
        return $key instanceof CacheKey ? $key->normalized : CacheKey::make($key)->normalized;
    }

    /**
     * @param iterable<string|\Stringable> $keys
     * @return array<string>
     */
    private static function keys(iterable $keys): array
    {
        $normalized = [];
        foreach ($keys as $key) {
            $normalized[] = self::normalize($key);
        }
        return $normalized;
    }

    private static function ttl(TimeInterval|\DateInterval|int|null $ttl): int|null
    {
        if ($ttl === null) {
            return null;
        }

        if ($ttl instanceof TimeInterval) {
            return $ttl->seconds === TimeInterval::max()->seconds ? null : $ttl->seconds;
        }
        if ($ttl instanceof \DateInterval) {
            return TimeInterval::instance($ttl)->seconds;
        }
        return $ttl;
    }

    public function getItem(\Stringable|string $key): CacheItemInterface
    {
        return $this->pool->getItem(self::normalize($key));
    }

    /**
     * @param iterable<string|\Stringable> $keys
     * @return iterable<CacheItemInterface>
     */
    public function getItems(iterable $keys = []): iterable
    {
        return $this->pool->getItems(self::keys($keys));
    }

    public function hasItem(\Stringable|string $key): bool
    {
        return $this->pool->hasItem(self::normalize($key));
    }

    public function deleteItem(\Stringable|string $key): bool
    {
        return $this->pool->deleteItem(self::normalize($key));
    }

    /**
     * @param iterable<string|\Stringable> $keys
     */
    public function deleteItems(iterable $keys): bool
    {
        return $this->pool->deleteItems(self::keys($keys));
    }

    public function save(CacheItemInterface $item): bool
    {
        return $this->pool->save($item);
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        return $this->pool->saveDeferred($item);
    }

    public function commit(): bool
    {
        return $this->pool->commit();
    }
}
