<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cache\Psr6;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class CacheItemPoolProxy implements CacheItemPoolInterface
{
    public function __construct(
        private readonly CacheItemPoolInterface $pool,
        private readonly string $namespace = '',
    ) {
    }

    private function normalize(string $key): string
    {
        return $this->namespace ? $this->namespace . '.' . $key : $key;
    }

    public function getItem(string $key): CacheItemInterface
    {
        return $this->pool->getItem($this->normalize($key));
    }

    /**
     * @param array<string> $keys
     * @return array<string, CacheItemInterface>
     */
    public function getItems(array $keys = []): iterable
    {
        return \array_combine($keys, [...$this->pool->getItems(\array_map($this->normalize(...), $keys))]);
    }

    public function hasItem(string $key): bool
    {
        return $this->pool->hasItem($this->normalize($key));
    }

    public function clear(): bool
    {
        return $this->pool->clear();
    }

    public function deleteItem(string $key): bool
    {
        return $this->pool->deleteItem($this->normalize($key));
    }

    public function deleteItems(array $keys): bool
    {
        return $this->pool->deleteItems(\array_map($this->normalize(...), $keys));
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
